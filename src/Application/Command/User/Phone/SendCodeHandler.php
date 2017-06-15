<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Phone;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class SendCodeHandler
{
    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var DateTimeInterface */
    private $dateTime;

    /**
     * @param UserEventPhoneRepositoryInterface $userEventPhoneRepository
     * @param DateTimeInterface                 $dateTime
     */
    public function __construct(
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        DateTimeInterface $dateTime
    ) {
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(SendCode $sendCode)
    {
    }
}
