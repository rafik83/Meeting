<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Rule\Applier;
use Proximum\Vimeet\Application\Components\Rule\Strategy\SetNullStrategy;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetCatalogView;
use Proximum\Vimeet\Domain\Model\SheetDataView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class SheetManager
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var TypeRepositoryInterface
     */
    private $typeRepository;

    /**
     * @var array
     */
    private $cache;

    /**
     * SheetManager constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param RuleRepositoryInterface        $ruleRepository
     * @param TypeRepositoryInterface        $typeRepository
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        RuleRepositoryInterface $ruleRepository,
        TypeRepositoryInterface $typeRepository
    ) {
        $this->participantRepository = $participantRepository;
        $this->ruleRepository        = $ruleRepository;
        $this->typeRepository        = $typeRepository;
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
        $this->applyRule($user, $sheet);

        return new SheetCatalogView($sheet->getId(), $sheet->getData(), $sheet->getType()->getSheetTemplate());
    }

    /**
     * Applay rule rules to sheet data
     *
     * @param User  $user
     * @param Sheet $sheet
     */
    public function applyRule(User $user, Sheet $sheet)
    {
        $applier = new Applier();
        $applier->apply($this->getRuleToApply($sheet, $user), $sheet, new SetNullStrategy());
    }

    /**
     * Get the most prioritary rule rule to apply
     *
     * @param Sheet $sheet
     * @param User  $user
     *
     * @return Rule
     */
    public function getRuleToApply(Sheet $sheet, User $user)
    {
        // Check cache
        if (isset($this->cache[$sheet->getType()->getId()])) {
            return $this->cache[$sheet->getType()->getId()];
        }

        // Get types of sheet the user have for this event
        $types = $this->typeRepository->getTypesByUser($sheet->getEvent(), $user);

        // Get related rules
        $rules = [];
        foreach ($types as $type) {
            $rules = array_merge($rules, $this->ruleRepository->getBySeerTypeAndSeeableType($type, $sheet->getType()));
        }

        // Sort rules by priority
        usort($rules, function (Rule $one, Rule $another) {
            return $one->getPriority() < $another->getPriority() ?
                1  : $one->getPriority() > $another->getPriority() ? -1 : 0;
        });

        // Update cache
        $this->cache[$sheet->getType()->getId()] = isset($rules[0]) ? $rules[0] : null;

        return $this->cache[$sheet->getType()->getId()];
    }
}
