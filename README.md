# e9li SEO

Lightweight SEO for Kirby 5: an SEO tab for pages and the site, meta tags, Open Graph,
Twitter Card, canonical and hreflang links, JSON-LD (Organization/WebSite), `robots.txt`
and `sitemap.xml`. No Panel preview, no AI — just the fields and the output.

## Installation

```bash
composer require e9li/kirby-seo
```

Or copy the folder to `site/plugins/seo`.

## Usage

Add the tabs to your blueprints:

```yaml
# site/blueprints/site.yml
tabs:
    seo: seo/site

# site/blueprints/pages/default.yml (or a shared layout)
tabs:
    seo: seo/page
```

Render the tags in your templates:

```php
<?php snippet('seo/head') ?>   <!-- inside <head> -->
<?php snippet('seo/schemas') ?> <!-- JSON-LD, anywhere -->
```

In templates you can also use the page method: `$page->meta()->title()`,
`$page->meta()->ogImageUrl()`, `$page->meta()->robots()` …
(see `src/Meta.php`).

## Fallback logic

| Output | Page | Site | Fallback |
| --- | --- | --- | --- |
| `<title>` | `metaTitle` | `metaTemplate` pattern | `defaultTitleTemplate` option |
| description | `metaDescription` | `metaDescription` | – |
| OG title | – | `ogTemplate` pattern | meta title |
| OG description | `ogDescription` | `ogDescription` | meta description |
| OG image | `ogImage` | `ogImage` | – |
| robots | `robotsIndex` … (`default/true/false`) | site value | allow |

Title templates support `{{ page.title }}`, `{{ site.title }}` and `{{ separator }}`.

## Options (`config.php`)

```php
return [
    'e9li.kirby-seo.canonicalBase' => 'https://example.com',
    'e9li.kirby-seo.titleSeparator' => '|',
    'e9li.kirby-seo.defaultTitleTemplate' => '{{ page.title }} {{ separator }} {{ site.title }}',
    'e9li.kirby-seo.ogImage' => ['width' => 1200, 'height' => 630],
    'e9li.kirby-seo.ogType' => 'website',
    'e9li.kirby-seo.robots' => [
        'enabled' => true,
        'content' => null, // string or array of lines
    ],
    'e9li.kirby-seo.sitemap' => [
        'enabled' => true,
        'excludeTemplates' => ['error'],
        'excludePages' => [],
    ],
    'e9li.kirby-seo.schemas' => [
        'organization' => true,
        'website' => true,
    ],
];
```

## Field compatibility

Field names match the ones used by tobimori/kirby-seo (`metaTitle`, `metaDescription`,
`ogImage`, `robotsIndex`, …), so existing content keeps working when migrating.
Not carried over: Panel preview, Search Console, AI fields, per-page title templates
and the `metaInherit` multiselect — page fields simply fall back to the site settings
when empty or set to «Standard».

## License

MIT
