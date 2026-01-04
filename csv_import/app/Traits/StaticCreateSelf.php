<?php

namespace App\Traits;

use ReflectionClass;

trait StaticCreateSelf
{
   public static function create(array $values): static
    {
        $rc = new ReflectionClass(static::class);
        $ctor = $rc->getConstructor();

        // Если конструктора нет — тогда можно просто создать
        if (!$ctor) {
            return new static();
        }

        $args = [];
        foreach ($ctor->getParameters() as $p) {
            $name = $p->getName();

            if (array_key_exists($name, $values)) {
                $args[$name] = $values[$name];
                continue;
            }

            if ($p->isDefaultValueAvailable()) {
                $args[$name] = $p->getDefaultValue();
                continue;
            }

            throw new \InvalidArgumentException("Missing required DTO field: {$name}");
        }

        // PHP 8+: named args
        return new static(...$args);
    }
}
