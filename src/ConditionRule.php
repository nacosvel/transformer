<?php

namespace Nacosvel\Transformer;

use Closure;

class ConditionRule extends Rule
{
    public function __construct(
        public Closure $condition,
        public Closure $rule,
    ) {
        //
    }

    public function handle(array $context, Closure $next)
    {
        ['original' => $originals, 'target' => $targets] = $context;

        if (call_user_func($this->condition, $originals, $targets)) {
            $targets = call_user_func($this->rule, $originals, $targets);
        }

        return $next(['original' => $originals, 'target' => $targets]);
    }
}
