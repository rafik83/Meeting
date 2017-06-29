<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Token\UserEventToken;

use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenUnexpectedTypeException;
use Proximum\Vimeet\Domain\Repository\Token\UserEventTokenRepositoryInterface;

class ConfirmAgendaHandler
{
    const ALREADY_CONFIRMED = 'already_confirmed';
    const CONFIRMED = 'confirmed';

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
     * @return string
     * @throws UserEventTokenUnexpectedTypeException
     */
    public function handle(ConfirmAgenda $command)
    {
        if ($command->userEventToken->isConfirmed()) {
            return self::ALREADY_CONFIRMED;
        }

        if (!$command->userEventToken->isAgendaConfirmation()) {
            throw new UserEventTokenUnexpectedTypeException();
        }

        $command->userEventToken->confirm($this->dateTime);

        $this->userEventTokenRepository->set($command->userEventToken);

        return self::CONFIRMED;
    }
}
