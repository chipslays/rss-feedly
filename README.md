# Feedly

Simple RSS feed reader for PHP.

## Installation

```bash
composer require chipslays/rss-feedly
```

## Usage

```php
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

$items = Feedly::filter($rss, [
    // exclude content where the title or description contain the following words
    'except' => [
        // equal: /\blgbt\b/ui
        'lgbt',

        // supports regex
        '/politic/iu',

        // if all these words are in the title or description
        ['apple', 'epic games', '/\blaw/ui']
    ],

    // sort items by priority
    'priority' => [

        // high priority
        'high' => [['bitcoin', 'rate'], 'google', ''],

        // medium priority
        'medium' => ['telegram', 'durov'],

        // low priority
        'low' => [],

        // elements without priority will be added to the end
    ],
]);

foreach ($items->all() as $index => $item) {
    print_r("#{$index}. {$item['title']}: {$item['description']}" . PHP_EOL);
}
```

## Examples

More exampels can be found [`here`](examples).

## License

MIT.
