<?php

namespace Skeletal\Http;

use Stringable;

class Request implements Stringable
{
    public function __construct($globalGET, $globalPOST, $globalCOOKIE, $globalFILES, $globalSERVER)
    {
        debug($globalGET);
        debug($globalPOST);
        debug($globalCOOKIE);
        debug($globalFILES);
        debug($globalSERVER);
    }

    public static function getInfo()
    {
        return 'hello';
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
