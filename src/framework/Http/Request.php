<?php

namespace Skeletal\Http;

use Stringable;

class Request implements Stringable
{

    protected $uuid;

    private $storage = [];

    public function __construct(...$globals)
    {
        $this->uuid = uniqid();
        // debug($globalGET);
        // debug($globalPOST);
        // debug($globalCOOKIE);
        // debug($globalFILES);
        // debug($globalSERVER);
        $this->storage = collect()->merge(...$globals);
    }

    public function getInfo()
    {
        return $this->uuid;
    }

    public function test()
    {
        return 'test success';
    }

    public static function createFromGlobals(...$globals): static
    {
        return new static(
            ...$globals
        );
    }

    public function __call(string $method, array $arguments)
    {
        return $this->storage->$method(...$arguments);
    }

    public function __get(string $key)
    {
        return $this->storage->get($key);
    }

    public function __set(string $key, ?mixed $value)
    {
        return $this->storage->set($key, $value);
    }

    public function __toString(): string
    {
        return json_encode([
            'class' => $this::class
        ]);
    }
}
