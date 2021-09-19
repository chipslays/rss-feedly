<?php

use Feedly\Feedly;

require __DIR__ . '/../vendor/autoload.php';

Feedly::config([
    'useragent' => 'FeedFetcher-Google',
    'cache.dir' => __DIR__ . '/storage/cache',
    'cache.expire' => '+30 minutes',
]);

if (!$rss = Feedly::get('https://lenta.ru/rss/news')) {
    throw new Exception("Error while getting feed content.", 1);
}

foreach ($rss->items()->all() as $index => $item) {
    print_r("#{$index}. {$item['title']}: {$item['description']}" . PHP_EOL);
}

// or
$rss->items()->each(function ($item, $index) {
    print_r("#{$index}. {$item['title']}: {$item['description']}" . PHP_EOL);
});