<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

class ImportSheetHandler
{
    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        \DateTimeInterface $dateTime
    ) {
        $this->dateTime = $dateTime;
    }

    /**
     * @param Event            $event
     * @param Type             $type
     * @param RegistrationView $registrationView
     */
    public function handle(Event $event, Type $type, RegistrationView $registrationView): void
    {

    }
}
