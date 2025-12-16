<?php

namespace Nacosvel\Transformer;

use Closure;

class MetadataRule extends Rule
{
    public function handle(array $context, Closure $next)
    {
        ['original' => $originals, 'target' => $targets] = $context;

        foreach ($originals as $key => $original) {
            $targets[$key] = $original;
        }

        return $next(['original' => $originals, 'target' => $targets]);
    }
}
