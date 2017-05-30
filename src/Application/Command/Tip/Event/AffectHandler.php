<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class AffectHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;
    
    /**
     * AffectHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param Affect $affect
     */
    public function handle(Affect $affect)
    {
        $tip = $affect->tip;

        foreach ($affect->types as $type) {
            $tip->setType($type);
        }

        $this->tipRepository->setTypes($tip);
    }
}
