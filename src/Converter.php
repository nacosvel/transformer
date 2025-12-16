<?php

namespace Nacosvel\Transformer;

use Nacosvel\Pipeline\Hub;
use Nacosvel\Pipeline\Pipeline;

class Converter implements Contracts\Converter
{
    public static function convert(array $original, array $rules, array $default = []): array
    {
        $hub = new Hub();

        $hub->defaults(function (Pipeline $pipeline, $passable) use ($rules, $default) {
            return $pipeline->send([
                'original' => $passable,
                'target'   => $default,
            ])->through($rules)->then(function ($passable) {
                return $passable['target'];
            });
        });

        return $hub->pipe($original);
    }
}
