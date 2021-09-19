<?php

use Feedly\Feedly;

require __DIR__ . '/../vendor/autoload.php';

Feedly::config([
    'useragent' => 'FeedFetcher-Google',
    'cache.dir' => __DIR__ . '/storage/cache',
    'cache.expire' => '+30 minutes',
]);

$items = Feedly::aggregate([
    'https://lenta.ru/rss/news',
    'https://meduza.io/rss2/all',
], [
    'except' => ['blm', 'politic*'],
    'priority' => [
        'high' => ['google', 'tech*'],
    ],
]);

foreach ($items->all() as $index => $item) {
    print_r("#{$index}. {$item['title']}" . PHP_EOL);
}