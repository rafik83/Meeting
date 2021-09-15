<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Meeting;

use Proximum\Vimeet\Domain\View\Catalog\TypeView;
use Symfony\Component\Form\DataTransformerInterface;

class TypeViewTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($typeViews)
    {
        return $typeViews;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($typeViews)
    {
        $types = [];

        /** @var TypeView $typeView */
        foreach ($typeViews as $typeView) {
            $types[] = $typeView->id;
        }

        return $types;
    }
}
