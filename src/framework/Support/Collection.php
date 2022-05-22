<?php

namespace Skeletal\Support;

use ArrayAccess;
use Stringable;

use ArrayObject;
use Closure;

/**
 * Skeletal\Support\Collection
 * @author Ben Hirst
 *
 */
class Collection extends ArrayObject implements ArrayAccess, Stringable
{

    public function __construct(array|object $array = [])
    {
        parent::__construct($array, static::ARRAY_AS_PROPS);
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
        return new static($this->getArrayCopy());
    }

    public function keys(): static
    {
        return $this->array_keys();
    }

    public function merge(array ...$arrays): static
    {
        return $this->array_merge(...$arrays);
    }

    public function values(): static
    {
        return $this->array_values();
    }

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

    public function set(string|int $key, mixed $value): void
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
    }

    public function unset(string|int $key): void
    {
        if (!$this->get($key)) {
            // item doesnt exist, no need to do anything
            return;
        }

        $nest = explode('.', $key);
        if ($nest[0] === $key) {
            unset($this[$key]);
            return;
        }

        $result = $value = $this->get($resultKey = array_shift($nest));
        if ($result instanceof static) {
            $result = $result->getArrayCopy();
        }

        $removingKey = array_pop($nest);
        $value = $this->reducer($nest, $value, function (mixed $carry, mixed $key) {
            return $carry instanceof static || is_array($carry) ? $carry[$key] ?? null : null;
        });

        unset($value[$removingKey]);

        $value = $this->reducer(array_reverse($nest), $value, function (mixed $carry, mixed $key) {
            return [$key => $carry];
        });

        $this[$resultKey] = is_array($value) ? array_replace($result, $value) : $value;
    }

    protected function reducer(array $nest, mixed $value, callable $callback)
    {
        return array_reduce($nest, $callback, $value);
    }

    public function __call(string $method, array $arguments)
    {
        if (is_callable($method) && str_starts_with($method, 'array_')) {
            $this->exchangeArray(
                $method($this->getArrayCopy(), ...$arguments)
            );

            return $this;
        }

        throw new \BadMethodCallException($this::class . '->' . $method);
    }
}
