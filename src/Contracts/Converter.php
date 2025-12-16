<?php

namespace Nacosvel\Transformer\Contracts;

interface Converter
{
    public static function convert(array $original, array $rules, array $default = []): array;
}
