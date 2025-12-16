<?php

namespace Nacosvel\Transformer;

use Closure;

class InvokeRule extends Rule
{
    public function __construct(
        public Closure $transformer,
    ) {
        //
    }

    public function handle(array $context, Closure $next)
    {
        ['original' => $originals, 'target' => $targets] = $context;

        $targets = call_user_func($this->transformer, $originals, $targets);

        return $next(['original' => $originals, 'target' => $targets]);
    }
}
