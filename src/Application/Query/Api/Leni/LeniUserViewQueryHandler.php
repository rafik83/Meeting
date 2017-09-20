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

    /**
     * @param UserInfoGuesser              $userInfoGuesser
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param TypeNameResolver             $typeNameResolver
     * @param GroupNameResolver            $groupNameResolver
     */
    public function __construct(
        UserInfoGuesser $userInfoGuesser,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        TypeNameResolver $typeNameResolver,
        GroupNameResolver $groupNameResolver
    ) {
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->typeNameResolver = $typeNameResolver;
        $this->groupNameResolver = $groupNameResolver;
        $this->userInfoGuesser = $userInfoGuesser;
    }

    /**
     * @param LeniUserViewQuery $query
     *
     * @return LeniUserView
     */
    public function handle(LeniUserViewQuery $query): LeniUserView
    {
        $userLocale = $query->event->getAvailableLocale($query->user);

        $planning = $this->participantPlanningFormatter->formatPlanningByDayFromUserAndEventWithUnallocated(
            $query->user,
            $query->event,
            $userLocale
        );

        $userInfo = $this->userInfoGuesser->getUserInfoFromParticipant($query->user, $userLocale, $query->sheets);

        $days = [];
        foreach ($planning->days as $day) {
            $days[] = new LeniPlanningDayView($day);
        }

        $leniPlanning = new LeniPlanningView($days, $planning->unallocated);

        return new LeniUserView(
            $query->user->getId(),
            $this->groupNameResolver->resolve($query->event, $query->user, $query->sheets),
            $this->typeNameResolver->resolveWithPreloadedSheets($query->sheets, $query->event->getFallback()),
            $query->user->getEmail(),
            $userInfo['gender'],
            $userInfo['firstName'],
            $userInfo['lastName'],
            $userInfo['position'],
            $userInfo['phone'],
            $userInfo['mobile'],
            $leniPlanning
        );
    }
}
