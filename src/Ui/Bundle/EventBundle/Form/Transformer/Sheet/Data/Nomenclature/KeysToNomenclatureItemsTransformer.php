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

class KeysToNomenclatureItemsTransformer implements DataTransformerInterface
{
    /**
     * @var KeyToNomenclatureItemTransformer
     */
    private $transformer;

    /**
     * KeyToNomenclatureItemTransformer constructor.
     *
     * @param Nomenclature $nomenclature
     */
    public function __construct(Nomenclature $nomenclature)
    {
        $this->transformer = new KeyToNomenclatureItemTransformer($nomenclature);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (empty ($value)) {
            return $value;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('Array expected, %s given.', gettype($value)));
        }

        return array_map(function ($key) { $this->transformer->transform($key); }, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty ($value)) {
            return $value;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException(sprintf('Array expected, %s given.', gettype($value)));
        }

        return array_map(function (NomenclatureItem $item) { return $this->transform($item); }, $value);
    }
}
