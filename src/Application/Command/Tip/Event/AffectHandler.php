<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Exception\Tip\TipAlreadyAffectedToEventException;
use Proximum\Vimeet\Application\Exception\Tip\TipNotFoundException;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class AffectHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;
    
    /**
     * AffectHandler constructor.
     *
     * @param TipRepositoryInterface  $tipRepository
     * @param TypeRepositoryInterface $typeRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository, TypeRepositoryInterface $typeRepository)
    {
        $this->tipRepository  = $tipRepository;
        $this->typeRepository = $typeRepository;
    }

    /**
     * @param Affect $affect
     *
     * @throws TipAlreadyAffectedToEventException
     */
    public function handle(Affect $affect)
    {
        $tip = $this->tipRepository->getByTipTranslationId($affect->tip->id, $affect->event);

        if ($tip->getTypes()) {
        if (null === $tip) {
            throw new TipNotFoundException();
        }
            throw new TipAlreadyAffectedToEventException();
        }

        foreach ($affect->types as $typeView) {
            $type = $this->typeRepository->getById($typeView->id);
            $tip->setType($type);
        }

        $this->tipRepository->setTypes($tip);
    }
}
