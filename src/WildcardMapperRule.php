<?php

namespace Nacosvel\Transformer;

use Closure;
use Nacosvel\TransformerMapper\WildcardMapper;

class WildcardMapperRule extends Rule
{
    public function __construct(
        public array $rules,
    ) {
        //
    }

    public function handle(array $context, Closure $next)
    {
        ['original' => $originals, 'target' => $targets] = $context;

        $wildcardMapper = new WildcardMapper($originals);
        $mappers        = $wildcardMapper->mapper($this->rules);
        foreach ($mappers as $key => $value) {
            $targets[$key] = $value;
        }

        return $next(['original' => $originals, 'target' => $targets]);
    }
}
