<?php

namespace Nacosvel\Transformer\Contracts;

use Closure;

interface Transformer
{
    public function handle(array $context, Closure $next);
}
