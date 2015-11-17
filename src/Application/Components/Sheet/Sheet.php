<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet as SheetModel;
use Proximum\Vimeet\Domain\Model\SheetDataView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class Sheet
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param SheetModel $sheet
     * @param User       $user
     *
     * @return SheetDataView
     */
    public function getSheetDataView(SheetModel $sheet, User $user)
    {
        return new SheetDataView(
            $sheet->getId(),
            $sheet->getEvent(),
            $sheet->getType(),
            $sheet->getParticipants(),
            $sheet->getData(),
            $sheet->getPackageData(),
            $sheet->getBillingData(),
            $this->participantRepository->getParticipantViewsBySheet($sheet->getId()),
            $this->participantRepository->getParticipantForUserAndSheet($user, $sheet)
        );
    }
}
