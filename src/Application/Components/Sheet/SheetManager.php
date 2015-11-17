<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Rule\RuleManager;
use Proximum\Vimeet\Application\Components\Rule\Strategy\SetNullStrategy;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetCatalogView;
use Proximum\Vimeet\Domain\Model\SheetDataView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class SheetManager
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var RuleManager
     */
    private $ruleManager;

    /**
     * SheetManager constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param RuleManager                    $ruleManager
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        RuleManager $ruleManager
    ) {
        $this->participantRepository = $participantRepository;
        $this->ruleManager           = $ruleManager;
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
     * @return SheetCatalogView
     */
    public function getSheetDataViewByUser(User $user, Sheet $sheet)
    {
        // Get rule
        $rule = $this->ruleManager->getRule($sheet, $user);

        // Apply rule
        $this->ruleManager->apply($rule, $sheet, new SetNullStrategy());

        return new SheetCatalogView(
            $sheet->getId(),
            $sheet->getData(),
            $sheet->getType()->getSheetTemplate(),
            $sheet->getType()->getParticipantTemplate(),
            $sheet->getParticipants()->toArray()
        );
    }
}
