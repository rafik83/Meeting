<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Phone;

use Proximum\Vimeet\Application\Exception\User\Phone\CodeAlreadyValidatedException;
use Proximum\Vimeet\Application\Exception\User\Phone\CodeNotValidException;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class ValidateCodeHandler
{
    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param ValidateCode $command
     *
     * @throws CodeAlreadyValidatedException
     * @throws CodeNotValidException
     */
    public function handle(ValidateCode $command)
    {
        if ($command->userEventPhone->isValidated()) {
            throw new CodeAlreadyValidatedException('The UserEventPhone is already validated');
        }

        if ($command->code !== $command->userEventPhone->getCode()) {
            throw new CodeNotValidException('The given code is not valid');
        }

        $command->userEventPhone->validate($this->dateTime);
        $this->userEventPhoneRepository->set($command->userEventPhone);
    }
}
