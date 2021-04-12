<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

class UniqueValuesTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {

        return array_unique($value);
    }
}
