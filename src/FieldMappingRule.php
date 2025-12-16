<?php

namespace Nacosvel\Transformer;

use Closure;

class FieldMappingRule extends Rule
{
    public function __construct(
        public string $original,
        public string $target,
        public ?Closure $transformer = null,
    ) {
        //
    }

    public function handle(array $context, Closure $next)
    {
        ['original' => $originals, 'target' => $targets] = $context;

        foreach ($originals as $key => $value) {
            if ($key == $this->original) {
                $targets[$this->target] = is_callable($this->transformer) ? call_user_func($this->transformer, $value) : $value;
            }
        }

        return $next(['original' => $originals, 'target' => $targets]);
    }
}
