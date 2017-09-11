<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameters;

use Proximum\Vimeet\Domain\Exception\Event\ExtraParameters\AnExtraParametersAlreadyExistForThisTypeAndEventException;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameters;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParametersRepositoryInterface;

class CreateHandler
{
    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ExtraParametersRepositoryInterface */
    private $extraParametersRepository;

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
     * @param Create $create
     *
     * @throws AnExtraParametersAlreadyExistForThisTypeAndEventException
     */
    public function handle(Create $create)
    {
        if (null !== $this->extraParametersRepository->findByEventAndType($create->event, $create->type)) {
            throw new AnExtraParametersAlreadyExistForThisTypeAndEventException();
        }

        $extraParameter = new ExtraParameters(
            $create->event,
            $create->type,
            $create->name,
            $create->value,
            $this->dateTime
        );

        $this->extraParametersRepository->add($extraParameter);
    }
}
