<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImageValidator extends TemplateObjectValidator
{
    /**
     * @var TemplateProductGuesser
     */
    private $templateProductGuesser;

    /**
     * ImageValidator constructor.
     *
     * @param TemplateProductGuesser $templateProductGuesser
     */
    public function __construct(TemplateProductGuesser $templateProductGuesser)
    {
        $this->templateProductGuesser = $templateProductGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof TemplateObject\Image) {
            $this->checkRequired($value, $constraint);
            $this->checkHasPayableOption($value, $constraint);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\Image
            && true === $object->getOption('required')
            && $object instanceof TemplateObject\ContentObjectInterface
        ) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath($constraint->key)
                ->validate($object->getImage(), new NotBlank());
        }
    }

    /**
     * @param TemplateObject $object
     * @param Constraint     $constraint
     */
    protected function checkHasPayableOption(TemplateObject $object, Constraint $constraint)
    {
         if ($this->templateProductGuesser->hasPayableOption($object)) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath('selectedProduct')
                ->validate($object->getSelectedProduct(), new NotBlank());
        }
    }
}
