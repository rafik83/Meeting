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

class UpdateHandler
{
    /** @var ExtraParametersRepositoryInterface */
    private $extraParametersRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param ExtraParametersRepositoryInterface $extraParametersRepository
     * @param \DateTimeInterface                 $dateTime
     */
    public function __construct(
        ExtraParametersRepositoryInterface $extraParametersRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->extraParametersRepository = $extraParametersRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Update $command
     */
    public function handle(Update $command)
    {
        $command->extraParameters->update($command->name, $command->value, $this->dateTime);

        $this->extraParametersRepository->set($command->extraParameters);
    }
}
