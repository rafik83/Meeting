<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class RankedCollectionTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return [];
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('"array" expected, "%s" given.', gettype($value)));
        }

        return array_map(function ($item, $rank) {
            return ['item' => $item, 'rank' => $rank];
        }, array_values($value), array_keys($value));
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return [];
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('"array" expected, "%s" given.', gettype($value)));
        }

        usort($value, function (array $one, array $another) {
            return $one['rank'] - $another['rank'];
        });

        return array_map(function (array $ranked) {
            return $ranked['item'];
        }, $value);
    }
}
