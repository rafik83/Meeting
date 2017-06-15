<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Token\UserEventToken;

use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenAlreadyConfirmedException;
use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenUnexpectedTypeException;
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
     *
     * @throws UserEventTokenAlreadyConfirmedException
     * @throws UserEventTokenUnexpectedTypeException
     */
    public function handle(ConfirmAgenda $command)
    {
        if ($command->userEventToken->isConfirmed()) {
            throw new UserEventTokenAlreadyConfirmedException();
        }

        if (!$command->userEventToken->isAgendaConfirmation()) {
            throw new UserEventTokenUnexpectedTypeException();
        }

        $command->userEventToken->confirm($this->dateTime);

        $this->userEventTokenRepository->set($command->userEventToken);
    }
}
