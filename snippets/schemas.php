<?php

/**
 * Renders JSON-LD structured data (Organization and WebSite).
 *
 * @var \Kirby\Cms\App $kirby
 * @var \Kirby\Cms\Page $page
 * @var \Kirby\Cms\Site $site
 */

use E9li\Seo\Meta;

$meta = Meta::for($page);
$schemas = [];

if (Meta::option('schemas.organization', true)) {
    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $meta->ogSiteName(),
        'url' => Meta::baseUrl(),
    ];

    $sameAs = $meta->sameAs();
    if ($sameAs !== []) {
        $organization['sameAs'] = $sameAs;
    }

    $schemas[] = $organization;
}

if (Meta::option('schemas.website', true)) {
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $site->title()->value(),
        'url' => Meta::baseUrl(),
    ];
}

foreach ($schemas as $schema): ?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endforeach ?>
