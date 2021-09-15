<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject\Item;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\ConstraintValidator;

class ItemValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var Item $value */
        if (null !== $value->getRawTitle()) {
            if (is_array($value->getRawTitle())) {
                $this->validateArray($value->getRawTitle(), $constraint);
            } else {
                $this->validateString($value->getRawTitle(), $constraint);
            }
        }
    }

    /**
     * @param array      $titles
     * @param Constraint $constraint
     */
    public function validateArray(array $titles, Constraint $constraint)
    {
        foreach ($titles as $locale => $title) {
            $this->validateString($title, $constraint);
        }
    }

    /**
     * @param string     $title
     * @param Constraint $constraint
     */
    public function validateString($title, Constraint $constraint)
    {
        $this->context
            ->getValidator()
            ->inContext($this->context)
            ->atPath($constraint->key . 'title')
            ->validate($title, new Length(['min' => 0, 'max' => 100]));
    }
}
