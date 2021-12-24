<?php

use Feedly\Feedly;

require __DIR__ . '/../vendor/autoload.php';

$rss = new Feedly([
    'useragent' => 'FeedFetcher-Google',
    'cacheDir' => __DIR__ . '/cache',
    'cacheTtl' => '+30 minutes',
]);

$posts = $rss
    ->get('https://www.rt.com/rss/news/')
    ->except(['Trump'], ['title'])
    ->except(['Politics'], ['category'])
    ->priority([
        [100, ['Google', 'Tesla', 'Durov'], ['title', 'description']],
        [Feedly::DEFAULT_PRIORITY + 1, ['Apple'], ['description']],
    ]);

// get posts in the last 6 hours
$posts = $posts->where('date', '>', strtotime('-6 hours'));

// can use foreach or `each` method
$posts->each(function ($post) {
    echo $post['title'] . PHP_EOL;
});


