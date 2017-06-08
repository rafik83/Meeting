<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Token\UserEventToken;

use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;

class ConfirmAgendaHandler
{
    /** @var UserEventTokenRepositoryInterface */
    private $userEventTokenRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param UserEventTokenRepositoryInterface $userEventTokenRepository
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        UserEventTokenRepositoryInterface $userEventTokenRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->userEventTokenRepository = $userEventTokenRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param ConfirmAgenda $command
     */
    public function handle(ConfirmAgenda $command)
    {
        $command->userEventToken->confirm($this->dateTime);

        $this->userEventTokenRepository->set($command->userEventToken);
    }
}
