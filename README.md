# 📢 Feedly

Simple RSS feed reader for PHP.

## Installation

```bash
composer require chipslays/rss-feedly
```

## Usage

Simple example: 

```php
use Feedly\Feedly;

require __DIR__ . '/vendor/autoload.php';

$feed = (new Feedly)->get('https://www.rt.com/rss/news/');

foreach ($feed->posts as $post) {
    echo $post['title'] . PHP_EOL;
}
```

And much advanced example:

```php
use Feedly\Feedly;

require __DIR__ . '/vendor/autoload.php';

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

foreach ($posts as $post) {
    // ...
}
```

## Examples

More exampels can be found [`here`](examples).

## License

MIT.
