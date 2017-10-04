<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Api\Leni;

use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\View\Api\Leni\LeniPlanningDayView;
use Proximum\Vimeet\Application\View\Api\Leni\LeniPlanningView;
use Proximum\Vimeet\Application\View\Api\Leni\LeniUserView;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class LeniUserViewQueryHandler
{
    /** @var ParticipantPlanningFormatter */
    private $participantPlanningFormatter;

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var UserInfoGuesser */
    private $userInfoGuesser;

    /** @var CategoryNameResolver */
    private $categoryNameResolver;

    /**
     * @param UserInfoGuesser              $userInfoGuesser
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param TypeNameResolver             $typeNameResolver
     * @param CategoryNameResolver         $categoryNameResolver
     * @param GroupNameResolver            $groupNameResolver
     */
    public function __construct(
        UserInfoGuesser $userInfoGuesser,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        TypeNameResolver $typeNameResolver,
        CategoryNameResolver $categoryNameResolver,
        GroupNameResolver $groupNameResolver
    ) {
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->typeNameResolver             = $typeNameResolver;
        $this->categoryNameResolver         = $categoryNameResolver;
        $this->groupNameResolver            = $groupNameResolver;
        $this->userInfoGuesser              = $userInfoGuesser;
    }

    /**
     * @param LeniUserViewQuery $query
     *
     * @return LeniUserView
     */
    public function handle(LeniUserViewQuery $query): LeniUserView
    {
        $userLocale = $query->event->getAvailableLocale($query->user->getLocale());

        $planning = $this->participantPlanningFormatter->formatPlanningByDayFromUserAndEventWithUnallocated(
            $query->user,
            $query->event,
            $userLocale
        );

        $userInfo = $this->userInfoGuesser->getUserInfoFromParticipant(
            $query->user,
            $userLocale,
            $query->sheets,
            false
        );

        $days = [];
        foreach ($planning->days as $day) {
            $days[] = new LeniPlanningDayView($day);
        }

        $leniPlanning = new LeniPlanningView($days, $planning->unallocated);

        $type = $this->typeNameResolver->resolveTypeWithPreloadedSheets($query->sheets);
        $category = $this->categoryNameResolver->resolveCategoryForPreloadSheets($query->sheets);

        $gender = '';
        if ($userInfo['gender'] === 'man') {
            $gender = 'M';
        } elseif ($userInfo['gender'] === 'woman') {
            $gender = 'MME';
        }

        return new LeniUserView(
            $query->user->getId(),
            $this->groupNameResolver->resolve($query->event, $query->user, $query->sheets),
            $type->getId(),
            $category !== null ? $category->getId() : null,
            $query->user->getEmail(),
            $gender,
            $userInfo['firstName'],
            $userInfo['lastName'],
            $userInfo['position'],
            $userInfo['phone'],
            $userInfo['mobile'],
            $leniPlanning
        );
    }
}
