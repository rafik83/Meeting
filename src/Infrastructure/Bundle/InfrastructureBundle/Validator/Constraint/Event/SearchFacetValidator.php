<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Event;

use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SearchFacetValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($searchFacet, Constraint $constraint)
    {
        if ($searchFacet instanceof SearchFacet && true === $searchFacet->isEnabled()) {
            foreach ($searchFacet->getTranslations() as $translation) {
                if (empty($translation->getLabel())) {
                    $this->context
                        ->buildViolation('validators.searchFacet.translations.label.empty')
                        ->atPath('enabled')
                        ->addViolation();
                    break;
                }
            }
        }
    }
}
