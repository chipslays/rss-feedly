<?php

namespace Feedly;

use Feedly\Types\RssFeed;
use Feedly\Exceptions\FeedlyException;
use Chipslays\Collection\Collection;
use SimpleXMLElement;

class Feedly
{
    protected static $curl;

    protected const CACHE_FILENAME = '%s/feedly.%s.xml';

    protected const DEFAULT_CACHE_EXPIRE_TIME = '+30 minutes';

    /**
     * @var Collection
     */
    protected static $config = [
        'useragent' => 'FeedFetcher-Google',
        'cache' => [
            'dir' => null,
            'expire' => self::DEFAULT_CACHE_EXPIRE_TIME, // timestamp|string
        ],
    ];

    public function __construct(array $config = [])
    {
        $this->config($config);
    }

    /**
     * @param string $url
     * @param string $user
     * @param string $password
     * @return RSS|bool
     *
     * @throws FeedlyException
     */
    public static function get(string $url, $user = null, $password = null)
    {
        $cacheDir = rtrim(self::config('cache.dir'), '/\\');
        $cacheFile = sprintf(self::CACHE_FILENAME, (string) $cacheDir, md5(serialize(func_get_args())));

        if ($cacheDir && file_exists($cacheFile)) {
            $cacheExpire = self::config('cache.expire', self::DEFAULT_CACHE_EXPIRE_TIME);
            if (
                time() - @filemtime($cacheFile) <=
                (is_string($cacheExpire)
                    ? strtotime($cacheExpire) - time()
                    : $cacheExpire)
                && $response = @file_get_contents($cacheFile)
            ) {
                return self::handleResponse($response);
            }
        }

        $response = self::request($url, $user, $password);

        if ($cacheDir) {
            file_put_contents($cacheFile, $response);
        }

        return self::handleResponse($response);
    }

    /**
     * Agreagate multiple RSS urls and create common feed.
     *
     * @param array $urls Array of RSS urls
     * @param array $filter Array of filter config
     * @return Collection
     */
    public static function aggregate(array $urls, array $filter = null)
    {
        $items = [];

        foreach ($urls as $url) {
            if(!$rss = self::get($url)) {
                continue;
            }

            $items = array_merge($items, $rss->items()->all());
        }

        if ($filter) {
            return self::filter($items, $filter);
        }

        return new Collection($items);
    }

    protected static function handleResponse($response)
    {
        if (!$response) {
            return false;
        }

        $xml = new SimpleXMLElement($response, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NOCDATA);

        if ($xml->channel) {
            return self::handleRssResponse($xml);
        }

        // @todo: add Atom supports?

        throw new FeedlyException('Unknown RSS format while handle response.', 1);
    }

    /**
     * @param SimpleXMLElement $xml
     * @return void
     */
    protected static function handleRssResponse(SimpleXMLElement $xml)
    {
        // self::adjustNamespaces($xml);

        // foreach ($xml->channel->item as $item) {
        //     self::adjustNamespaces($item);

        //     $item->url = (string) $item->link;
        //     if (isset($item->{'dc:date'})) {
        //         $item->timestamp = strtotime($item->{'dc:date'});
        //     } elseif (isset($item->pubDate)) {
        //         $item->timestamp = strtotime($item->pubDate);
        //     }

        //     $item->image = null;
        //     if (isset($item->enclosure)) {
        //         $item->image = $item->enclosure['url'];
        //     }
        // }

        return new RssFeed($xml);
    }

    /**
     * @param string $url
     * @return string|bool
     */
    public static function request(string $url)
    {
        self::$curl = self::$curl ?: curl_init();

        curl_setopt(self::$curl, CURLOPT_URL, $url);

        curl_setopt(self::$curl, CURLOPT_USERAGENT, self::config('useragent'));
        curl_setopt(self::$curl, CURLOPT_HEADER, false);
        curl_setopt(self::$curl, CURLOPT_TIMEOUT, 20);
        curl_setopt(self::$curl, CURLOPT_ENCODING, '');
        curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(self::$curl, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec(self::$curl);

        return curl_errno(self::$curl) === 0 && curl_getinfo(self::$curl, CURLINFO_HTTP_CODE) === 200
            ? $result
            : false;
    }

    /**
     * Set CURL option.
     *
     * @param integer $option
     * @param mixed $value
     * @return void
     */
    public static function setOpt(int $option, mixed $value)
    {
        curl_setopt(self::$curl, $option, $value);
    }

    /**
     * @param  SimpleXMLElement $el
     * @return void
     */
    private static function adjustNamespaces($el)
    {
        foreach ($el->getNamespaces(true) as $prefix => $ns) {
            $children = $el->children($ns);
            foreach ($children as $tag => $content) {
                $el->{$prefix . ':' . $tag} = $content;
            }
        }
    }

    /**
     * @param string|array $key Array of key & value for set, String for getting value by key name.
     * @param mixed $value
     * @return mixed
     */
    public static function config($key, $default = null)
    {
        if (is_array(self::$config)) {
            self::$config = new Collection(self::$config);
        }

        if (!is_array($key)) {
            return self::$config->get($key, $default);
        }

        foreach ($arr = $key as $key => $value) {
            self::$config->set($key, $value);
        }
    }

    /**
     * Filter RSS items.
     *
     * @param array|RssFeed $items
     * @param array $filter
     * @return Collection Collection of items
     */
    public static function filter($items, array $filter = [])
    {
        $filter = array_replace_recursive([
            'except' => [],
            'priority' => [
                'high' => [],
                'medium' => [],
                'low' => [],
            ],
        ], $filter);

        $items = is_array($items) ? $items : $items->items()->all();

        $filtered = [];
        foreach ($items as $item) {
            // check except item
            foreach ($filter['except'] as $pattern) {
                if (self::itemContain($item, $pattern)) {
                    continue 2;
                }
            }

            $filtered[] = $item;
        }

        $highPriority = [];
        $mediumPriority = [];
        $lowPriority = [];
        $noPriority = [];

        foreach ($filtered as $item) {
            foreach ($filter['priority']['high'] as $pattern) {
                if (self::itemContain($item, $pattern)) {
                    $highPriority[] = $item;
                    continue 2;
                }
            }

            foreach ($filter['priority']['medium'] as $pattern) {
                if (self::itemContain($item, $pattern)) {
                    $mediumPriority[] = $item;
                    continue 2;
                }
            }

            foreach ($filter['priority']['low'] as $pattern) {
                if (self::itemContain($item, $pattern)) {
                    $lowPriority[] = $item;
                    continue 2;
                }
            }

            $noPriority[] = $item;
        }

        $prioritized = array_merge($highPriority, $mediumPriority, $lowPriority, $noPriority);

        return new Collection($prioritized);
    }

    protected static function itemContain($item, $pattern)
    {
        $title = trim($item['title']);
        $description = trim($item['description']) ?? '';

        $contain = function ($text, $pattern) {
            $result = @preg_match($pattern, $text);
            if ($result === false) {
                if (mb_substr($pattern, -1) == '*') {
                    if (preg_match("~{$pattern}~iu", $text)) {
                        return true;
                    }
                } else {
                    if (preg_match("~\b{$pattern}\b~iu", $text)) {
                        return true;
                    }
                }
            } elseif ($result > 0) {
                return true;
            }

            return false;
        };

        // as array (all values should be contain in item)
        if (is_array($pattern)) {
            $containCount = 0;
            foreach ($pattern as $value) {
                if (self::itemContain($item, $value)) {
                    $containCount++;
                }
            }
            if ($containCount >= count($pattern)) {
                return true;
            }
            return false;
        } else {
            // check in title
            if ($contain($title, $pattern)) {
                return true;
            }
            // check in description
            if ($contain($description, $pattern)) {
                return true;
            }
            return false;
        }
    }
}
