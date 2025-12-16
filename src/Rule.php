<?php

namespace Nacosvel\Transformer;

use Closure;
use Nacosvel\Transformer\Contracts\Transformer;

abstract class Rule implements Transformer
{
    abstract public function handle(array $context, Closure $next);
}
