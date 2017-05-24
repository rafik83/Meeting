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
    
    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * AffectHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     * @param \DateTimeInterface     $dateTime
     */
    public function __construct(TipRepositoryInterface $tipRepository, \DateTimeInterface $dateTime)
    {
        $this->tipRepository = $tipRepository;
        $this->dateTime      = $dateTime;
    }

    public function handle(Affect $command)
    {
        $tip = $command->tip;

        foreach ($command->types as $type) {
            $tip->setType($type);
        }

        $this->tipRepository->setTypes($tip);
    }
}
