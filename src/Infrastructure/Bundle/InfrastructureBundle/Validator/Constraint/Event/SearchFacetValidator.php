<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Event;

use Proximum\Vimeet\Application\Command\Event\SearchFacet\Update;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SearchFacetValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($command, Constraint $constraint)
    {
        if ($command instanceof Update) {
            foreach ($command->searchFacets as $searchFacet) {
                if ($searchFacet->isEnabled() === true) {
                    foreach ($searchFacet->getTranslations() as $translation) {
                        if (empty($translation->getLabel())) {
                            $this->context
                                ->buildViolation('erreur')
                                ->atPath('searchFacets.translations')
                                ->addViolation();
                        }
                    }
                }
            }
        }
    }
}
