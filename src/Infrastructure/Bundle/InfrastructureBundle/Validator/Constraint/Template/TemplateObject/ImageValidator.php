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
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\Image && $this->templateProductGuesser->hasPayableOption($object)) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath($constraint->key)
                ->validate($object->getSelectedProduct(), new NotBlank());
        }
    }
}
