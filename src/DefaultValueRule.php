<?php

namespace Nacosvel\Transformer;

use Closure;

class DefaultValueRule extends Rule
{
    public function __construct(
        public string $target,
        public mixed $transformer = null,
    ) {
        //
    }

    public function handle(array $context, Closure $next)
    {
        ['original' => $originals, 'target' => $targets] = $context;

        if (is_callable($this->transformer)) {
            $targets[$this->target] = call_user_func($this->transformer, $originals[$this->target] ?? null);
        } else {
            $targets[$this->target] = $this->transformer;
        }

        return $next(['original' => $originals, 'target' => $targets]);
    }
}
