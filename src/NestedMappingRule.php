<?php

namespace Nacosvel\Transformer;

use Closure;

class NestedMappingRule extends Rule
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

        $value = array_reduce($this->segments($this->original), function ($current, $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return null;
            }
            return $current[$key];
        }, $originals);

        $targets = $this->updateContext(
            $targets,
            $this->segments($this->target),
            is_callable($this->transformer) ? call_user_func($this->transformer, $value) : $value
        );

        return $next(['original' => $originals, 'target' => $targets]);
    }

    private function updateContext(array $targets, array $path, mixed $value): mixed
    {
        $key = array_shift($path);

        if ($key === null) {
            return $value;
        }

        if (!array_key_exists($key, $targets)) {
            $targets[$key] = [];
        }

        $targets[$key] = $this->updateContext(
            is_array($child = $targets[$key]) ? $child : [],
            $path,
            $value
        );

        return $targets;
    }

    private function segments(string $path): array
    {
        return array_map(
            fn($s) => ctype_digit($s) ? (int)$s : $s,
            explode('.', $path)
        );
    }
}
