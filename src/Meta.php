<?php

namespace E9li\Seo;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Toolkit\Str;

class Meta
{
    public const ROBOTS_TYPES = ['index', 'follow', 'archive', 'imageindex', 'snippet'];

    public function __construct(protected Page $page)
    {
    }

    public static function for(Page $page): static
    {
        return new static($page);
    }

    public static function option(string $key, mixed $default = null): mixed
    {
        return App::instance()->option('e9li.kirby-seo.' . $key, $default);
    }

    public static function baseUrl(): string
    {
        $base = static::option('canonicalBase');

        return $base ? rtrim($base, '/') : App::instance()->url('index');
    }

    protected function site(): Site
    {
        return $this->page->site();
    }

    protected function value(string $field): string
    {
        return trim((string) $this->page->content()->get($field)->value());
    }

    protected function siteValue(string $field): string
    {
        return trim((string) $this->site()->content()->get($field)->value());
    }

    protected function render(string $template): string
    {
        return Str::template($template, [
            'page' => $this->page,
            'site' => $this->site(),
            'separator' => static::option('titleSeparator', '|'),
        ]);
    }

    public function title(): string
    {
        $custom = $this->value('metaTitle');
        if ($custom !== '') {
            return $custom;
        }

        $template = $this->siteValue('metaTemplate');
        if ($template === '') {
            $template = static::option(
                'defaultTitleTemplate',
                '{{ page.title }} | {{ site.title }}'
            );
        }

        return $this->render($template);
    }

    public function description(): string
    {
        return $this->value('metaDescription') !== ''
            ? $this->value('metaDescription')
            : $this->siteValue('metaDescription');
    }

    public function canonicalUrl(): string
    {
        return static::canonicalUrlFor($this->page);
    }

    public static function canonicalUrlFor(Page $page, ?string $languageCode = null): string
    {
        $url = $page->url($languageCode);

        $base = static::option('canonicalBase');
        if (!$base) {
            return $url;
        }

        $kirbyUrl = App::instance()->url('index');
        if (!Str::startsWith($url, $kirbyUrl)) {
            return $url;
        }

        return rtrim($base, '/') . '/' . ltrim(Str::after($url, $kirbyUrl), '/');
    }

    /**
     * @return array<string, bool> robots type => allowed
     */
    public function robots(): array
    {
        $result = [];
        foreach (static::ROBOTS_TYPES as $type) {
            $field = 'robots' . Str::ucfirst($type);
            $value = $this->value($field);

            if ($value === 'true') {
                $result[$type] = true;
            } elseif ($value === 'false') {
                $result[$type] = false;
            } else {
                $result[$type] = $this->siteValue($field) !== 'false';
            }
        }

        return $result;
    }

    public function robotsString(): string
    {
        $directives = [];
        foreach ($this->robots() as $type => $allowed) {
            if (!$allowed) {
                $directives[] = 'no' . $type;
            }
        }

        return implode(', ', $directives);
    }

    public function ogTitle(): string
    {
        $template = $this->siteValue('ogTemplate');
        if ($template !== '') {
            return $this->render($template);
        }

        return $this->title();
    }

    public function ogDescription(): string
    {
        return $this->value('ogDescription') !== ''
            ? $this->value('ogDescription')
            : ($this->siteValue('ogDescription') !== ''
                ? $this->siteValue('ogDescription')
                : $this->description());
    }

    public function ogSiteName(): string
    {
        return $this->siteValue('ogSiteName') !== ''
            ? $this->siteValue('ogSiteName')
            : $this->site()->title()->value();
    }

    public function ogType(): string
    {
        return static::option('ogType', 'website');
    }

    public function ogImage(): ?File
    {
        return $this->page->content()->get('ogImage')->toFile()
            ?? $this->site()->content()->get('ogImage')->toFile();
    }

    public function cropOgImage(): bool
    {
        $pageValue = $this->value('cropOgImage');
        if ($pageValue === 'true') {
            return true;
        }
        if ($pageValue === 'false') {
            return false;
        }

        $siteValue = $this->siteValue('cropOgImage');
        if ($siteValue !== '') {
            return $siteValue === 'true';
        }

        return true;
    }

    public function ogImageUrl(): ?string
    {
        $file = $this->ogImage();
        if ($file === null) {
            return null;
        }

        if ($this->cropOgImage()) {
            return $file->crop(
                (int) static::option('ogImage.width', 1200),
                (int) static::option('ogImage.height', 630)
            )->url();
        }

        return $file->url();
    }

    public function twitterCard(): string
    {
        return $this->ogImageUrl() !== null ? 'summary_large_image' : 'summary';
    }

    /**
     * @return string[] social profile URLs for JSON-LD sameAs
     */
    public function sameAs(): array
    {
        $social = $this->site()->content()->get('socialMedia')->toObject();
        $urls = [];

        foreach (['facebook', 'instagram', 'linkedin', 'x', 'youtube'] as $network) {
            $url = trim((string) $social->{$network}()->value());
            if ($url !== '') {
                $urls[] = $url;
            }
        }

        return $urls;
    }
}
