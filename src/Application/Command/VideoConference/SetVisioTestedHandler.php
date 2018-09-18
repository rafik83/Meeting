<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class SetVisioTestedHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $userEventExtraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ExtraDataRepositoryInterface $userEventExtraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(SetVisioTested $setVisioTested): void
    {
        $visioTestedExtraData = $this->userEventExtraDataRepository->getExtraDataForEventNameAndUser(
            $setVisioTested->event,
            Type::VISIO_TESTED,
            $setVisioTested->user
        );

        if (!$visioTestedExtraData instanceof ExtraData) {
            $this->userEventExtraDataRepository->add(
                new ExtraData($setVisioTested->user, $setVisioTested->event, Type::VISIO_TESTED, '1', $this->dateTime)
            );
        }
    }
}
