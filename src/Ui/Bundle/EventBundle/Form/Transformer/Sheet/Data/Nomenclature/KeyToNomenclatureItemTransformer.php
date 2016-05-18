<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class KeyToNomenclatureItemTransformer implements DataTransformerInterface
{
    /**
     * @var Nomenclature
     */
    private $nomenclature;

    /**
     * KeyToNomenclatureItemTransformer constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->nomenclature = $nomenclature;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('Array expected, %s given.', gettype($value)));
        }

        if (empty ($value)) {
            return $value;
        }

        $items = $this->nomenclature->getLastLevel();

        return array_map(function ($key) use ($items) {
            if ($item = self::findByKey($items, $key)) {
                return $item;
            }

            throw new TransformationFailedException(sprintf('"%s" key not found, available key are "%s"', $key, implode(', ', array_keys($items))));
        }, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('Array expected, %s given.', gettype($value)));
        }

        if (empty ($value)) {
            return $value;
        }

        return array_map(function (NomenclatureItem $item) {
            return $item->getKey();
        }, $value);
    }

    /**
     * @param NomenclatureItem[] $items
     * @param string             $key
     *
     * @return NomenclatureItem
     */
    private static function findByKey(array $items, $key)
    {
        foreach ($items as $item) {
            if ($item->getKey() === $key) {
                return $item;
            }
        }

        return null;
    }
}
