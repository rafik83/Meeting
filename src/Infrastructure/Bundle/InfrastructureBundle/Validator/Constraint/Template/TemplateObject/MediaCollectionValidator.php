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

class MediaCollectionValidator extends TemplateObjectValidator
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
        if ($value instanceof TemplateObject\MediaCollection) {
            $this->checkRequired($value, $constraint);
            $this->checkHasPayableOption($value, $constraint);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\MediaCollection
            && true === $object->getOption('required')
            && $object instanceof TemplateObject\ContentObjectInterface
        ) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath($constraint->key)
                ->validate($object->getMedias(), new NotBlank());
        }
    }

    /**
     * @param TemplateObject\MediaCollection $object
     * @param Constraint                     $constraint
     */
    protected function checkHasPayableOption(TemplateObject\MediaCollection $object, Constraint $constraint)
    {
        if (count($object->getNotEmptyMedias()) > 0 && $this->templateProductGuesser->hasPayableOption($object)) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath('selectedProduct')
                ->validate($object->getSelectedProduct(), new NotBlank());
        }
    }
}
