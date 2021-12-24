# 📢 Feedly

Simple RSS feed reader for PHP.

## Installation

```bash
composer require chipslays/rss-feedly
```

## Usage

```php
use Feedly\Feedly;

require __DIR__ . '/vendor/autoload.php';

$feed = (new Feedly)->get('https://www.rt.com/rss/news/');

foreach ($feed->posts as $post) {
    echo $post['title'] . PHP_EOL;
}
```

## Examples

More exampels can be found [`here`](examples).

## License

MIT.
