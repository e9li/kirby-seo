<?php

use E9li\Seo\Meta;
use Kirby\Cms\App;
use Kirby\Http\Response;

load([
    'E9li\\Seo\\Meta' => __DIR__ . '/src/Meta.php',
]);

App::plugin('e9li/kirby-seo', [
    'options' => [
        // Base URL for canonical/OG/sitemap URLs, e.g. 'https://example.com'.
        // Falls back to the page URLs Kirby generates.
        'canonicalBase' => null,

        // Fallback title pattern when site.metaTemplate is empty.
        // Placeholders: {{ page.title }}, {{ site.title }}, {{ separator }}
        'defaultTitleTemplate' => '{{ page.title }} {{ separator }} {{ site.title }}',
        'titleSeparator' => '|',

        // OG image crop size when cropOgImage is on.
        'ogImage' => [
            'width' => 1200,
            'height' => 630,
        ],

        // Default og:type.
        'ogType' => 'website',

        // robots.txt
        'robots' => [
            'enabled' => true,
            // null = sensible default; string or array of lines to override.
            'content' => null,
        ],

        // sitemap.xml
        'sitemap' => [
            'enabled' => true,
            'excludeTemplates' => ['error'],
            'excludePages' => [],
        ],

        // JSON-LD output of the seo/schemas snippet.
        'schemas' => [
            'organization' => true,
            'website' => true,
        ],
    ],
    'blueprints' => [
        'seo/page' => __DIR__ . '/blueprints/tabs/page.yml',
        'seo/site' => __DIR__ . '/blueprints/tabs/site.yml',
    ],
    'snippets' => [
        'seo/head' => __DIR__ . '/snippets/head.php',
        'seo/schemas' => __DIR__ . '/snippets/schemas.php',
    ],
    'translations' => [
        'de' => require __DIR__ . '/translations/de.php',
        'de-ch' => require __DIR__ . '/translations/de-ch.php',
        'en' => require __DIR__ . '/translations/en.php',
        'fr' => require __DIR__ . '/translations/fr.php',
        'it' => require __DIR__ . '/translations/it.php',
    ],
    'pageMethods' => [
        'meta' => function () {
            return Meta::for($this);
        },
    ],
    'routes' => [
        [
            'pattern' => 'robots.txt',
            'method' => 'GET',
            'language' => '*',
            'action' => function () {
                if (!Meta::option('robots.enabled', true)) {
                    $this->next();
                }

                $content = Meta::option('robots.content');
                if ($content === null) {
                    $content = [
                        'User-agent: *',
                        'Allow: /',
                        'Disallow: /panel/',
                        'Disallow: /kirby/',
                    ];
                }
                if (is_array($content)) {
                    $content = implode(PHP_EOL, $content);
                }

                if (Meta::option('sitemap.enabled', true)) {
                    $content .= PHP_EOL . 'Sitemap: ' . Meta::baseUrl() . '/sitemap.xml';
                }

                return new Response(trim($content) . PHP_EOL, 'text/plain');
            },
        ],
        [
            'pattern' => 'sitemap.xml',
            'method' => 'GET',
            'language' => '*',
            'action' => function () {
                if (!Meta::option('sitemap.enabled', true)) {
                    $this->next();
                }

                $kirby = App::instance();
                $languages = $kirby->languages();
                $excludeTemplates = Meta::option('sitemap.excludeTemplates', []);
                $excludePages = Meta::option('sitemap.excludePages', []);

                $pages = $kirby->site()->index()->filter(function ($page) use ($excludeTemplates, $excludePages) {
                    if (in_array((string) $page->intendedTemplate(), $excludeTemplates, true)) {
                        return false;
                    }
                    if (in_array($page->id(), $excludePages, true)) {
                        return false;
                    }
                    return Meta::for($page)->robots()['index'];
                });

                $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
                $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . PHP_EOL;

                foreach ($pages as $page) {
                    $xml .= '  <url>' . PHP_EOL;
                    $xml .= '    <loc>' . htmlspecialchars(Meta::canonicalUrlFor($page), ENT_XML1) . '</loc>' . PHP_EOL;
                    $xml .= '    <lastmod>' . $page->modified('c') . '</lastmod>' . PHP_EOL;
                    foreach ($languages as $language) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="' . $language->code() . '" href="' . htmlspecialchars(Meta::canonicalUrlFor($page, $language->code()), ENT_XML1) . '"/>' . PHP_EOL;
                    }
                    if ($languages->count() > 0 && ($default = $kirby->defaultLanguage())) {
                        $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . htmlspecialchars(Meta::canonicalUrlFor($page, $default->code()), ENT_XML1) . '"/>' . PHP_EOL;
                    }
                    $xml .= '  </url>' . PHP_EOL;
                }

                $xml .= '</urlset>' . PHP_EOL;

                return new Response($xml, 'application/xml');
            },
        ],
    ],
]);
