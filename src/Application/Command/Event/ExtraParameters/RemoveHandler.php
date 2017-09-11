<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameters;

use Proximum\Vimeet\Domain\Repository\Event\ExtraParametersRepositoryInterface;

class RemoveHandler
{
    /** @var ExtraParametersRepositoryInterface */
    private $extraParametersRepository;

    /**
     * @param ExtraParametersRepositoryInterface $extraParametersRepository
     */
    public function __construct(ExtraParametersRepositoryInterface $extraParametersRepository)
    {
        $this->extraParametersRepository = $extraParametersRepository;
    }

    /**
     * @param Remove $remove
     */
    public function handle(Remove $remove)
    {
        $this->extraParametersRepository->remove($remove->extraParameters);
    }
}
