<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Application\Exception\Tip\TipNotAffectOnEventException;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class RemoveHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /**
     * RemoveHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    public function handle(Remove $remove)
    {
        foreach ($remove->tip->getTypes() as $type) {
            if ($type->getEvent() !== $remove->event) {
                throw new TipNotAffectOnEventException();
            }
        }

        $this->tipRepository->removeTipForEvent($remove->tip);
    }
}
