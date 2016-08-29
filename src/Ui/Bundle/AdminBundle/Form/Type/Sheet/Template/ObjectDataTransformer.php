<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\DataTransformerInterface;

class ObjectDataTransformer implements DataTransformerInterface
{
    /**
     * @var TemplateData
     */
    private $templateData;

    /**
     * ObjectDataTransformer constructor.
     *
     * @param TemplateData $templateData
     */
    public function __construct(TemplateData $templateData)
    {
        $this->templateData = $templateData;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($keys)
    {
        return array_map(function ($key) {
            return $this->templateData->getObject($key);
        }, $keys);
    }

    /**
     * @param TemplateObject[] $objects
     *
     * @return array of string
     */
    public function reverseTransform($objects)
    {
        $serializedObject = [];
        foreach ($objects as $object) {
            $serializedObject[] = $object->getKey();
        }

        return $serializedObject;
    }
}
