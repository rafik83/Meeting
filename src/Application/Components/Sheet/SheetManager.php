<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Apply\Applier;
use Proximum\Vimeet\Application\Components\Sheet\Apply\Strategy\SetNullStrategy;
use Proximum\Vimeet\Domain\Model\See;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetDataView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SeeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class SheetManager
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var SeeRepositoryInterface
     */
    private $seeRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * SheetManager constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param SeeRepositoryInterface         $seeRepository
     * @param TypeRepositoryInterface        $typeRepository
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        SeeRepositoryInterface $seeRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->participantRepository = $participantRepository;
        $this->seeRepository              = $seeRepository;
        $this->typeRepository             = $typeRepository;
    }

    /**
     * @param Sheet $sheet
     * @param User       $user
     *
     * @return SheetDataView
     */
    public function getSheetDataView(Sheet $sheet, User $user)
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

    /**
     * @param User   $user
     * @param Sheet  $sheet
     *
     * @return SheetDataView
     */
    public function getSheetDataViewByUser(User $user, Sheet $sheet)
    {
        $this->applyVisibility($user, $sheet);

        return $this->getSheetDataView($sheet, $user);
    }

    /**
     * Applay see rules to sheet data
     *
     * @param User  $user
     * @param Sheet $sheet
     */
    private function applyVisibility(User $user, Sheet $sheet)
    {
        $applier = new Applier();
        $applier->apply($this->getSeeToApply($sheet, $user), $sheet, new SetNullStrategy());
    }

    /**
     * Get the most prioritary see rule to apply
     *
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return See
     */
    private function getSeeToApply(Sheet $sheet, User $user)
    {
        // Get types of sheet the user the user have for this event
        $types = $this->typeRepository->getTypesByUser($sheet->getEvent(), $user);

        // Get related rules
        $sees = [];
        foreach ($types as $type) {
            $sees = array_merge($sees, $this->seeRepository->getBySeerTypeAndSeeableType($type, $sheet->getType()));
        }

        usort($sees, function (See $one, See $another) {
            return $one < $another ? -1  : $one > $another ? 1 : 0;
        });

        return $sees[0];
    }
}
