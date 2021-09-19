<?php

namespace Feedly\Types;

use Chipslays\Arr\Arr;
use Chipslays\Collection\Collection;
use SimpleXMLElement;

class RssFeed
{
    /**
     * @param SimpleXMLElement $xml
     */
    protected $xml;

    /**
     * @param Collection $rss
     */
    protected $rss;

    /**
     * @param Collection $rss
     */
    protected $items = [];

    public function __construct(SimpleXMLElement $xml)
    {
        $this->xml = $xml;

        $this->rss = new Collection(json_decode(json_encode($xml), true)['channel'] ?? []);

        $items = $this->rss->get('item', []);

        foreach ($items as $item) {
            $item = new Collection($item);

            $modifyItem = [];
            $modifyItem['title'] = $item->title ? trim($item->title) : null;
            $modifyItem['description'] = $item->description ? trim($item->description) : null;
            $modifyItem['date'] = $item->date ? @strtotime(trim($item->pubDate)) : null;
            $modifyItem['url'] = $item->link ? trim($item->link) : null;
            $modifyItem['category'] = $item->category ? trim($item->category) : null;
            $modifyItem['author'] = $item->author ? trim($item->author) : null;
            $modifyItem['guid'] = $item->guid ? trim($item->guid) : null;
            $modifyItem['comments'] = $item->comments ? trim($item->comments) : null;
            $modifyItem['source'] = $item->source ? trim($item->source) : null;

            if (!$item->enclosure) {
                $modifyItem['image'] = null;
            } else {
                $modifyItem['image'] = [
                    'url' => $item->enclosure['@attributes']['url'] ?? null,
                    'type' => $item->enclosure['@attributes']['type'] ?? null,
                    'length' => $item->enclosure['@attributes']['length'] ?? null,
                ];
            }

            $this->items[] = $modifyItem;
        }

        $this->items = new Collection($this->items);
    }

    /**
     * The name of the channel.
     * It's how people refer to your service.
     * If you have an HTML website that contains the same information as your RSS file,
     * the title of your channel should be the same as the title of your website.
     *
     * Example GoUpstate.com News Headlines
     *
     * @return string
     */
    public function title()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * The URL to the HTML website corresponding to the channel.
     *
     * Example: http://www.goupstate.com/
     *
     * @return string
     */
    public function link()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * Phrase or sentence describing the channel.
     *
     * Example: The latest news from GoUpstate.com, a Spartanburg Herald-Journal Web site.
     *
     * @return string
     */
    public function description()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * Specifies a GIF, JPEG or PNG image that can be displayed with the channel.
     *
     * @return array|null
     */
    public function image()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * The language the channel is written in.
     * This allows aggregators to group all Italian language sites,
     * for example, on a single page.
     * A list of allowable values for this element, as provided by Netscape.
     *
     * Example: en-us
     *
     * @return string|null
     */
    public function language()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * Copyright notice for content in the channel.
     *
     * Example: Copyright 2002, Spartanburg Herald-Journal
     *
     * @return string|null
     */
    public function copyright()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * Email address for person responsible for editorial content.
     *
     * Example: geo@herald.com (George Matesky)
     *
     * @return string|null
     */
    public function managingEditor()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * Email address for person responsible for technical issues relating to channel.
     *
     * Example: betty@herald.com (Betty Guernsey)
     *
     * @return string|null
     */
    public function webMaster()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * The publication date for the content in the channel.
     * For example, the New York Times publishes on a daily basis,
     * the publication date flips once every 24 hours.
     * That's when the pubDate of the channel changes.
     * All date-times in RSS conform to the Date and Time Specification of RFC 822,
     * with the exception that the year may be expressed
     * with two characters or four characters (four preferred).
     *
     * Example: Sat, 07 Sep 2002 0:00:01 GMT
     *
     * @return string|null
     */
    public function pubDate()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * The last time the content of the channel changed.
     *
     * Example: Sat, 07 Sep 2002 9:42:31 GMT
     *
     * @return string|null
     */
    public function lastBuildDate()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * Specify one or more categories that the channel belongs to.
     * Follows the same rules as the <item>-level category element.
     *
     * Example: <category>Newspapers</category>
     *
     * @return string|null
     */
    public function category()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * A string indicating the program used to generate the channel.
     *
     * Example: MightyInHouse Content System v2.3
     *
     * @return string|null
     */
    public function generator()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * A URL that points to the documentation for
     * the format used in the RSS file. It's probably a pointer to this page.
     * It's for people who might stumble across an RSS file
     * on a Web server 25 years from now and wonder what it is.
     *
     * Example: http://backend.userland.com/rss
     *
     * @return string|null
     */
    public function docs()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * Allows processes to register with a cloud to be notified of updates
     * to the channel, implementing a lightweight publish-subscribe
     * protocol for RSS feeds.
     *
     * Example: <cloud domain="rpc.sys.com" port="80" path="/RPC2" registerProcedure="pingMe" protocol="soap"/>
     *
     * @return string|null
     */
    public function cloud()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * ttl stands for time to live.
     * It's a number of minutes that indicates how long a channel
     * can be cached before refreshing from the source.
     *
     * Example: <ttl>60</ttl>
     *
     * @return string|null
     */
    public function ttl()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * Specifies a text input box that can be displayed with the channel.
     *
     * @return array|null
     */
    public function textInput()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * A hint for aggregators telling them which hours they can skip.
     *
     * Example: <ttl>60</ttl>
     *
     * @return array|null
     */
    public function skipHours()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * A hint for aggregators telling them which days they can skip.
     *
     * Example: <ttl>60</ttl>
     *
     * @return array|null
     */
    public function skipDays()
    {
        return $this->rss->get(__FUNCTION__);
    }

    /**
     * A channel may contain any number of <item>s.
     * An item may represent a "story" -- much like a story in a newspaper or magazine;
     * if so its description is a synopsis of the story, and the link points to the full story.
     * An item may also be complete in itself, if so, the description contains the text
     * (entity-encoded HTML is allowed), and the link and title may be omitted.
     * All elements of an item are optional, however at least one of title or description must be present.
     *
     * @return Collection
     */
    public function item()
    {
        return $this->items;
    }

    /**
     * Alias for `item()` method.
     *
     * @return Collection
     */
    public function items()
    {
        return $this->item();
    }

    /**
     * Get count of content items.
     *
     * @return int
     */
    public function count()
    {
        return $this->items->count();
    }

    public function toArray()
    {
        return $this->rss->toArray();
    }

    public function get($key, $default = null)
    {
        return $this->rss->get($key, $default);
    }

    public function __get($name)
    {
        return $this->rss->get($name);
    }

    public function xml()
    {
        return $this->xml;
    }
}