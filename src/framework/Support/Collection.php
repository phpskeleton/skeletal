<?php

namespace Skeletal\Support;

use ArrayAccess;
use Stringable;

use ArrayObject;

/**
 * Skeletal\Support\Collection
 * @author Ben Hirst
 *
 */
class Collection extends ArrayObject implements ArrayAccess, Stringable
{

    protected const ARRAY_SUM_AVG = 2;

    public function __construct(array|object $array = [])
    {
        parent::__construct($array, static::ARRAY_AS_PROPS);
    }

    public static function create(array|object $array = [])
    {
        return collect($array);
    }

    /**
     * Convert the collection to a string
     * @method __toString
     * @author Ben Hirst
     *
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->getArrayCopy());
    }

    public function all()
    {
        return $this->toArray();
    }

    /**
     *
     * Creates a new Collection instance containing the same array
     * @method copy
     * @author Ben Hirst
     *
     * @return Skeletal\Support\Collection
     */
    public function copy(): static
    {
        return collect($this->getArrayCopy());
    }

    public function avg(string $key)
    {
        return $this->sum($key, static::ARRAY_SUM_AVG);
    }

    public function sum(string $key, int $flags = 0)
    {
        $items = $this->pluck($key)->filter()->flatten();
        $sum = $items->array_sum();

        if ($flags === static::ARRAY_SUM_AVG) {
            return $sum/$items->count();
        }

        return $sum;
    }

    /**
     * Loop through each item in the collection
     */
    public function each(callable $callback): void
    {
        foreach ($this->getIterator() as $key => $value) {
            $callback($key, $value);
        }
    }

    /**
     * Loop through each item including nested arrays & objects in the collection
     */
    public function eachNested(callable $callback): void
    {
        foreach ($this->getIterator() as $key => $value) {
            if (is_array($value)) {
                $value = collect($value);
            }

            $callback($key, $value);

            if ($value instanceof static) {
                $value->eachNested($callback);
            }
        }
    }

    public function filter(?callable $callback = null)
    {
        return $this->array_filter($callback, ARRAY_FILTER_USE_BOTH);
    }

    public function flatten($depth = INF)
    {
        $result = [];

        foreach ($this->toArray() as $item) {
            $item = $item instanceof static ? $item->all() : $item;

            if (! is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : collect($item)->flatten($depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return collect($result);
    }

    /**
     * Creates a new Collection instance containing the keys of the array
     */
    public function keys(): static
    {
        return $this->array_keys();
    }

    public function map(callable $callback)
    {
        return array_map($callback, $this->getArrayCopy());
    }

    /**
     * Merges the current collection and any number of arrays into a new Collection instance
     */
    public function merge(array ...$arrays): static
    {
        return $this->array_merge(...$arrays);
    }

    /**
     * Return a collection containing only the keys provided
     */
    public function only(...$options)
    {
        if (isset($options[0]) && is_array($options[0])) {
            $options = $options[0];
        }

        $items = collect();
        foreach ($options as $index => $search) {
            $args = explode('.', $search);
            if ($value = $this->get($search)) {
                $key = array_pop($args);
                $items[$key] = $value;
            }
        }

        return $items;
    }

    /**
     * Pluck keys from items withn the collection and return them as a new collection
     */
    public function pluck(...$options)
    {
        if (isset($options[0]) && is_array($options[0])) {
            $options = $options[0];
        }

        $entries = $this->map(function ($item) use ($options) {
            if (is_array($item)) {
                $item = collect($item);
            }

            if (is_collection($item)) {
                return $item->only($options);
            }
        });

        $plucked = collect($entries)->filter()->values();

        if (count($options) <= 1) {
            $plucked = $plucked->flatten(1);
        }

        return $plucked;
    }

    /**
     * Sort the collection by keys
     */
    public function sortKeys(?callable $callback = null): static
    {
        if (is_callable($callback)) {
            $this->uksort($callback);
            return $this;
        }

        $this->ksort();
        return $this;
    }

    /**
     * Sort the collection by values
     */
    public function sortValues(?callable $callback = null): static
    {
        if (is_callable($callback)) {
            $this->uasort($callback);
            return $this;
        }

        $this->asort();
        return $this;
    }

    /**
     * Returns the current collection as an array
     */
    public function toArray(): array
    {
        $arr = $this->map(function ($item) {
            if (is_collection($item)) {
                return $item->toArray();
            }

            if (is_array($item)) {
                return collect($item)->toArray();
            }

            return $item;
        });

        return $arr;
    }

    /**
     * Returns the current collection with each nested array as another collection
     */
    public function toCollections(): static
    {
        $arr = $this->map(function ($item) {
            if (is_array($item)) {
                $newItem = collect($item)->toCollections();
                return $newItem;
            }

            return $item;
        });

        return collect($arr);
    }

    /**
     * Creates a new Collection instance containing the array values of the original
     */
    public function values(): static
    {
        return $this->array_values();
    }

    /**
     * Gets an item from the collection by its key
     */
    public function get(string|int $key): mixed
    {
        $nest = explode('.', $key);
        if (!$this->offsetExists($resultKey = array_shift($nest))) {
            return null;
        }

        $result = $this->offsetGet($resultKey);
        if (!$result instanceof static && is_array($result)) {
            $result = collect($result)->getArrayCopy();
        }

        $value = $this->reducer($nest, $result, function (mixed $carry, mixed $key) {
            return $carry instanceof static || is_array($carry) ? $carry[$key] ?? null : null;
        });

        return is_array($value) ? collect($value) : $value;
    }

    /**
     * Sets an item in the collection by its key
     */
    public function set(string|int $key, mixed $value): static
    {
        $nest = explode('.', $key);
        $result = $this->get($resultKey = array_shift($nest));

        if (!$result instanceof static) {
            $result = collect();
        }

        $result = $result->getArrayCopy();

        $newValue = $this->reducer(array_reverse($nest), $value, function (mixed $carry, mixed $key) {
            return [$key => $carry];
        });

        $this[$resultKey] = !is_array($value)
            ? (is_array($newValue) ? array_replace_recursive($result, $newValue) : $newValue)
            : (count($nest) ? array_replace($result, $newValue) : $newValue);

        // perhaps here we could decide whether to
        // exchange with toArray() or toCollections()
        $this->exchangeArray($this->toArray());
        return $this;
    }

    /**
     * Unsets an item in the collection by its key
     */
    public function unset(string|int $key)
    {
        $nest = explode('.', $key);
        $removingKey = array_pop($nest);

        if ($key === $removingKey) {
            unset($this[$key]);
            return;
        }

        if ($entry = $this->get($parentKey = implode('.', $nest))) {
            unset($entry[$removingKey]);
            $this->set($parentKey, $entry);
        }
    }

    /**
     * Internal function that calls array_reduce
     */
    protected function reducer(array $nest, mixed $value, callable $callback)
    {
        return array_reduce($nest, $callback, $value);
    }

    /**
     * Calls global array functions using the current collections array as the first argument
     */
    public function __call(string $method, array $arguments)
    {
        if (is_callable($method) && str_starts_with($method, 'array_')) {
            if (is_array($response = $method($this->toArray(), ...$arguments))) {
                return collect($response);
            }

            return $response;
        }

        throw new \BadMethodCallException($this::class . '->' . $method);
    }
}
