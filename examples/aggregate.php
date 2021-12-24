<?php

use Feedly\Feedly;

require __DIR__ . '/../vendor/autoload.php';

$rss = new Feedly([
    'useragent' => 'FeedFetcher-Google',
    'cacheDir' => __DIR__ . '/cache',
    'cacheTtl' => '+30 minutes',
]);

// sorted by date (newer first)
$posts = $rss->aggregate(['https://lenta.ru/rss/news', 'https://meduza.io/rss2/all']);

foreach ($posts as $post) {
    echo $post['title'] . PHP_EOL;
}


