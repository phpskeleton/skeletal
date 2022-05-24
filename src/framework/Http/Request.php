<?php

namespace Skeletal\Http;

use Stringable;

class Request implements Stringable
{

    protected $uuid;

    private $content = [];

    private $globals = [];

    public function __construct(...$globals)
    {
        $this->uuid = uniqid();

        $inputFile = file_get_contents('php://input');
        $body = json_decode($inputFile, true) ?? [];

        $this->content = collect($body);
        $this->globals = collect()->merge(...$globals);
    }

    public function getInfo()
    {
        return $this->uuid;
    }

    public function method()
    {
        return $this->storage->get('REQUEST_METHOD');
    }

    public function path()
    {
        return parse_url($this->storage->get('REQUEST_URI'), PHP_URL_PATH);;
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
        return $this->content->$method(...$arguments);
    }

    public function __get(string $key)
    {
        return $this->content->get($key);
    }

    public function __set(string $key, mixed $value)
    {
        return $this->content->set($key, $value);
    }

    public function __toString(): string
    {
        return json_encode([
            'class' => $this::class
        ]);
    }
}
