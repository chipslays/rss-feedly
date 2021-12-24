<?php

use Feedly\Feedly;

require __DIR__ . '/../vendor/autoload.php';

$rss = new Feedly([
    'useragent' => 'FeedFetcher-Google',
    'cacheDir' => __DIR__ . '/cache',
    'cacheTtl' => '+30 minutes',
]);

$feed = $rss->get('https://www.rt.com/rss/news/');

foreach ($feed->posts as $post) {
    // $post['title'] - string|null
    // $post['description'] - string|null
    // $post['date'] - timestamp|null
    // $post['url'] - string|null
    // $post['category'] - string|null
    // $post['author'] - string|null
    // $post['guid'] - string|null
    // $post['comments'] - string|null
    // $post['source'] - string|null
    // $post['image']['url'] - string|null
    // $post['image']['type'] - string|null
    // $post['image']['length'] - int|null
}


