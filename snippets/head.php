<?php

/**
 * Renders title, meta description, canonical, robots, Open Graph and
 * Twitter Card tags for the current page.
 *
 * @var \Kirby\Cms\App $kirby
 * @var \Kirby\Cms\Page $page
 * @var \Kirby\Cms\Site $site
 */

use E9li\Seo\Meta;

$meta = Meta::for($page);
$languages = $kirby->languages();
?>
<title><?= html($meta->title()) ?></title>
<?php if (($description = $meta->description()) !== ''): ?>
<meta name="description" content="<?= html($description) ?>">
<?php endif ?>
<link rel="canonical" href="<?= $meta->canonicalUrl() ?>">
<?php if (($robots = $meta->robotsString()) !== ''): ?>
<meta name="robots" content="<?= $robots ?>">
<?php endif ?>
<?php foreach ($languages as $language): ?>
<link rel="alternate" hreflang="<?= $language->code() ?>" href="<?= Meta::canonicalUrlFor($page, $language->code()) ?>">
<?php endforeach ?>
<?php if ($languages->count() > 0 && ($defaultLanguage = $kirby->defaultLanguage())): ?>
<link rel="alternate" hreflang="x-default" href="<?= Meta::canonicalUrlFor($page, $defaultLanguage->code()) ?>">
<?php endif ?>
<meta property="og:title" content="<?= html($meta->ogTitle()) ?>">
<meta property="og:type" content="<?= $meta->ogType() ?>">
<meta property="og:url" content="<?= $meta->canonicalUrl() ?>">
<meta property="og:site_name" content="<?= html($meta->ogSiteName()) ?>">
<?php if (($ogDescription = $meta->ogDescription()) !== ''): ?>
<meta property="og:description" content="<?= html($ogDescription) ?>">
<?php endif ?>
<?php if (($ogImage = $meta->ogImageUrl()) !== null): ?>
<meta property="og:image" content="<?= $ogImage ?>">
<?php if ($meta->cropOgImage()): ?>
<meta property="og:image:width" content="<?= (int) Meta::option('ogImage.width', 1200) ?>">
<meta property="og:image:height" content="<?= (int) Meta::option('ogImage.height', 630) ?>">
<?php endif ?>
<?php endif ?>
<meta name="twitter:card" content="<?= $meta->twitterCard() ?>">
<meta name="twitter:title" content="<?= html($meta->ogTitle()) ?>">
<?php if ($ogDescription !== ''): ?>
<meta name="twitter:description" content="<?= html($ogDescription) ?>">
<?php endif ?>
<?php if ($ogImage !== null): ?>
<meta name="twitter:image" content="<?= $ogImage ?>">
<?php endif ?>
