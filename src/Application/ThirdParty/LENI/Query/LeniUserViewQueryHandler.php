<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Query;

use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningDayView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniUserView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class LeniUserViewQueryHandler
{
    const GENDER_MAPPING = [
        Gender::MAN => 'M',
        Gender::WOMAN => 'MME',
    ];

    CONST ATTENDANCE = 'Inscrit';

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

    /** @var SheetRepositoryInterface */
    private $sheetRepository;
    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /**
     * @param UserInfoGuesser              $userInfoGuesser
     * @param SheetInfoGuesser             $sheetInfoGuesser
     * @param ParticipantPlanningFormatter $participantPlanningFormatter
     * @param TypeNameResolver             $typeNameResolver
     * @param CategoryNameResolver         $categoryNameResolver
     * @param GroupNameResolver            $groupNameResolver
     * @param SheetRepositoryInterface     $sheetRepository
     */
    public function __construct(
        UserInfoGuesser $userInfoGuesser,
        SheetInfoGuesser $sheetInfoGuesser,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        TypeNameResolver $typeNameResolver,
        CategoryNameResolver $categoryNameResolver,
        GroupNameResolver $groupNameResolver,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->userInfoGuesser              = $userInfoGuesser;
        $this->sheetInfoGuesser             = $sheetInfoGuesser;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->typeNameResolver             = $typeNameResolver;
        $this->categoryNameResolver         = $categoryNameResolver;
        $this->groupNameResolver            = $groupNameResolver;
        $this->sheetRepository              = $sheetRepository;
    }

    /**
     * @param LeniUserViewQuery $query
     *
     * @return LeniUserView
     * @throws SheetNotFoundException
     */
    public function handle(LeniUserViewQuery $query): LeniUserView
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($query->user, $query->event);

        $firstSheet = reset($sheets);

        if (false === $firstSheet) {
            throw new SheetNotFoundException('User must have at least one sheet');
        }

        $userLocale = $query->event->getAvailableLocale($query->user->getLocale());

        $planning = $this->participantPlanningFormatter->formatPlanningByDayFromUserAndEventWithUnallocated(
            $query->user,
            $query->event,
            $userLocale
        );

        $userInfo = $this->userInfoGuesser->getUserInfoFromParticipant(
            $query->user,
            $userLocale,
            $sheets,
            false
        );

        $days = [];
        foreach ($planning->days as $day) {
            $days[] = new LeniPlanningDayView($day);
        }

        $leniPlanning = new LeniPlanningView($days, $planning->unallocated);

        $type = $this->typeNameResolver->resolveTypeWithPreloadedSheets($sheets);
        $category = $this->categoryNameResolver->resolveCategoryForPreloadSheets($sheets);

        $gender = self::GENDER_MAPPING[$userInfo['gender']] ?? '';

        if ('' === $userInfo['country']) {
            $sheetInfos = $this->sheetInfoGuesser->guessSheetInfos($firstSheet);
            $userInfo['country'] = $sheetInfos[Tag::SHEET_COUNTRY] ?? '';
        }

        return new LeniUserView(
            $query->user->getId(),
            $this->groupNameResolver->resolve($query->event, $query->user, $sheets),
            $type->getId(),
            $category !== null ? $category->getId() : null,
            $query->user->getEmail(),
            $gender,
            $userInfo['firstName'],
            $userInfo['lastName'],
            $userInfo['position'],
            $userInfo['phone'],
            $userInfo['mobile'],
            $userInfo['country'],
            self::ATTENDANCE,
            $query->user->getLocale(),
            $leniPlanning
        );
    }
}
