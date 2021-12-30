<?php

use Feedly\Feedly;

require __DIR__ . '/../vendor/autoload.php';

$rss = new Feedly([
    'useragent' => 'FeedFetcher-Google',
    'cacheDir' => __DIR__ . '/cache',
    'cacheTtl' => '+30 minutes',
]);

$feed = $rss->get('https://lenta.ru/rss/news');

// default find Politics word in Title or Description
$posts = $feed->except(['Politics']);

// pass multiple words
// info: if at least one word is in the text (politics or putin or trump)
$posts = $feed->except(['Politi*', '/putin/iu', 'Trump']);

// find in Category
$posts = $feed->except(['Politics'], ['category']);

// find in Title or Category
$posts = $feed->except(['Politics'], ['title', 'category']);

// support regex
$posts = $feed->except(['/politics/iu'], ['category']);

// support asterisks (with case insensitive!)
$posts = $feed->except(['Polit*'], ['category']);

foreach ($posts as $post) {
    echo $post['title'] . PHP_EOL;
}


