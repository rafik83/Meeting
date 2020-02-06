<?php

namespace Proximum\Vimeet\Tests\Helper;

use ReflectionClass;

abstract class EntityId
{
    public static function setId($entity, int $id)
    {
        $reflection = new ReflectionClass(get_class($entity));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
