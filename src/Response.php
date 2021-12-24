<?php

namespace Feedly;

use Chipslays\Collection\Collection;

/**
 * @property Response posts
 */
class Response extends Collection
{
    public function except(array $excepts, array $in = ['title', 'description'])
    {
        $filteredItems = [];

        foreach ($this->items['posts'] ?? $this->items as $item) {
            foreach ($excepts as $pattern) {
                foreach ($in as $key) {
                    if ($this->itemContain($item, $key, $pattern)) {
                        continue 3;
                    }
                }
            }
            $filteredItems[] = $item;
        }

        return new static($filteredItems);
    }

    // int $priority, array $values, array $in = ['title', 'description']
    public function priority(array $priorites)
    {
        $filteredItems = [];

        foreach ($this->items['posts'] ?? $this->items as $item) {
            foreach ($priorites as $tmp) {
                [$priority, $values, $in] = $tmp;
                foreach ($values as $pattern) {
                    foreach ($in as $key) {
                        if ($this->itemContain($item, $key, $pattern)) {
                            $filteredItems[$priority][] = $item;
                            continue 4;
                        }
                    }
                }
            }
            $filteredItems[1e+15][] = $item;
        }

        ksort($filteredItems);
        $filteredItems = call_user_func_array('array_merge', $filteredItems);

        return new static($filteredItems);
    }

    protected function itemContain($item, $key, $pattern)
    {
        $searchString = $item[$key];

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
                if ($this->itemContain($item, $key, $value)) {
                    $containCount++;
                }
            }

            if ($containCount >= count($pattern)) {
                return true;
            }

            return false;
        } else {
            if ($contain($searchString, $pattern)) {
                return true;
            }

            return false;
        }
    }
}
