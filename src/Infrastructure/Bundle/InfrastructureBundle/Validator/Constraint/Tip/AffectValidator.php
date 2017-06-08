<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Tip;

use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AffectValidator extends ConstraintValidator
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /**
     * AffectValidator constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param Affect     $affect
     * @param Constraint $constraint
     */
    public function validate($affect, Constraint $constraint)
    {
        $tip = $this->tipRepository->getById($affect->tip->id);

        foreach ($affect->event->getLocales() as $locale) {
            if (!$tip->hasTranslation($locale)) {
                $this->context
                    ->buildViolation('validators.tip.affect.unavailable_locale')
                    ->atPath('tip')
                    ->addViolation();
                break;
            }
        }

        if ($this->tipRepository->isTipAffectedToEvent($tip, $affect->event)) {
            $this->context
                ->buildViolation('validators.tip.affect.already_affected')
                ->atPath('tip')
                ->addViolation();
        }
    }
}
