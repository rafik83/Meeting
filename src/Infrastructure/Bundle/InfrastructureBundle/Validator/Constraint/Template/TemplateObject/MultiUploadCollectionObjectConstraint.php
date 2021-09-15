<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Symfony\Component\Validator\Constraint;

class MultiUploadCollectionObjectConstraint extends Constraint
{
    /** @var string */
    public $key;

    public function validatedBy(): string
    {
        return MultiUploadCollectionObjectValidator::class;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
