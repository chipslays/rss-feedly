<?php

use Feedly\Feedly;

require __DIR__ . '/../vendor/autoload.php';

$rss = new Feedly([
    'useragent' => 'FeedFetcher-Google',
    'cacheDir' => __DIR__ . '/cache',
    'cacheTtl' => '+30 minutes',
]);

$feed = $rss->get('https://lenta.ru/rss/news');

// lower number of `priority` means higher priority
$posts = $feed->priority([
    // [required:priority, required:words, required:in]
    [100, ['Google', 'Tesla', 'Durov'], ['title', 'description']],

    [200, ['Design*', '/Photogr/iu'], ['title']],

    // adding posts with these words to end of the list
    [Feedly::DEFAULT_PRIORITY + 1, ['Politics', 'Putin', 'Trump'], ['title', 'description']],
]);

foreach ($posts as $post) {
    echo $post['title'] . PHP_EOL;
}


