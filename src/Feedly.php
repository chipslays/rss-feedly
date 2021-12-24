<?php

namespace Feedly;

use Chipslays\Collection\Collection;
use SimpleXMLElement;

class Feedly
{
    private ?Collection $config = null;

    protected $curl;

    protected const CACHE_FILENAME = '%s/feedly.%s.xml';

    public const DEFAULT_CACHE_EXPIRE_TIME = '+6 hours';

    public const DEFAULT_PRIORITY = 500;

    private array $defaultConfig = [
        'useragent' => 'FeedFetcher-Google',
        'cacheDir' => false,
        'cacheTtl' => self::DEFAULT_CACHE_EXPIRE_TIME,
    ];

    public function __construct(array $config = [])
    {
        $this->config = new Collection(array_merge($this->defaultConfig, $config));
    }

    /**
     * Get RSS feed.
     *
     * @param string $url
     * @return bool|Response
     */
    public function get(string $url)
    {
        $cacheDir = rtrim($this->config('cacheDir'), '/\\');
        $cacheFile = sprintf(self::CACHE_FILENAME, (string) $cacheDir, md5(serialize(func_get_args())));

        if ($cacheDir && file_exists($cacheFile)) {
            $cacheExpire = $this->config('cacheTtl', self::DEFAULT_CACHE_EXPIRE_TIME);
            if (
                time() - @filemtime($cacheFile) <=
                (is_string($cacheExpire)
                    ? strtotime($cacheExpire) - time()
                    : $cacheExpire)
                && $response = @file_get_contents($cacheFile)
            ) {
                return $this->handleResponse($response);
            }
        }

        $response = $this->request($url);

        if ($cacheDir) {
            file_put_contents($cacheFile, $response);
        }

        return $this->handleResponse($response);
    }

    /**
     * Aggreagate multiple RSS urls and create common feed.
     *
     * @param array $urls Array of RSS urls
     * @return Response
     */
    public function aggregate(array $urls)
    {
        $items = [];
        foreach ($urls as $url) {
            if (!$rss = $this->get($url)) {
                continue;
            }
            $items = array_merge($items, $rss->posts->all());
        }

        usort($items, function ($a, $b) {
            return $a['date'] <=> $b['date'];
        });

        return new Response($items);
    }

    /**
     * @param string|bool $rawResponse
     * @return Response|bool
     */
    protected function handleResponse($rawResponse)
    {
        if (!$rawResponse) {
            return false;
        }

        $xml = new SimpleXMLElement($rawResponse, LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NOCDATA);

        $response = json_decode(json_encode($xml), true)['channel'] ?? [];

        $items = [];
        foreach ($response['item'] ?? [] as $item) {
            $item = (object) $item;
            $newItem['title'] = $item->title ? trim($item->title) : null;
            $newItem['description'] = $item->description ? trim($item->description) : null;
            $newItem['date'] = $item->pubDate ? @strtotime(trim($item->pubDate)) : null;
            $newItem['url'] = $item->link ? trim($item->link) : null;
            $newItem['category'] = $item->category ? trim($item->category) : null;
            $newItem['author'] = $item->author ? trim($item->author) : null;
            $newItem['guid'] = $item->guid ? trim($item->guid) : null;
            $newItem['comments'] = $item->comments ? trim($item->comments) : null;
            $newItem['source'] = $item->source ? trim($item->source) : null;

            if (!$item->enclosure) {
                $newItem['image'] = null;
            } else {
                $newItem['image'] = [
                    'url' => $item->enclosure['@attributes']['url'] ?? null,
                    'type' => $item->enclosure['@attributes']['type'] ?? null,
                    'length' => $item->enclosure['@attributes']['length'] ?? null,
                ];
            }

            $items[] = $newItem;
        }

        $response['posts'] = new Response($items);
        unset($response['item']);

        return new Response($response);
    }

    /**
     * @param string $url
     * @return string|bool
     */
    protected function request(string $url)
    {
        $this->curl = $this->curl ?: curl_init();

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $this->config('useragent'));
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_ENCODING, '');
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);

        $result = curl_exec($this->curl);

        return  curl_errno($this->curl) === 0 && curl_getinfo($this->curl, CURLINFO_HTTP_CODE) === 200
            ? $result
            : false;
    }

    /**
     * Get value from config.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return $this->config->get($key, $default);
    }
}
