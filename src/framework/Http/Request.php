<?php

namespace Skeletal\Http;

use Stringable;

class Request implements Stringable
{

    protected $uuid;

    public function __construct($globalGET, $globalPOST, $globalCOOKIE, $globalFILES, $globalSERVER)
    {
        $this->uuid = uniqid();
        // debug($globalGET);
        // debug($globalPOST);
        // debug($globalCOOKIE);
        // debug($globalFILES);
        // debug($globalSERVER);
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

    public function __toString(): string
    {
        return json_encode([
            'class' => $this::class
        ]);
    }
}
