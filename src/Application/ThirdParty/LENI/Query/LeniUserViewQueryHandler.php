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
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningDayView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniUserView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
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
        $sheets = $this->getSheets($query->user, $query->event);

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

        $country = $userInfo['country'];

        if ('' === $country) {
            $sheetInfos = $this->sheetInfoGuesser->guessSheetInfos($firstSheet);
            $country = $sheetInfos[Tag::SHEET_COUNTRY] ?? '';
        }

        return new LeniUserView(
            $query->user->getId(),
            $firstSheet->isEnabled(),
            $this->groupNameResolver->resolve($query->event, $query->user, $sheets),
            $type->getId(),
            $category !== null ? $category->getId() : null,
            $query->user->getEmail(),
            $userInfo['gender'],
            $userInfo['firstName'],
            $userInfo['lastName'],
            $userInfo['position'],
            $userInfo['phone'],
            $userInfo['mobile'],
            $country,
            $query->user->getLocale(),
            $leniPlanning,
            $this->getPreviousLeniUserId($query)
        );
    }

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return Sheet[]
     */
    private function getSheets(User $user, Event $event): array
    {
        $sheets = $this->sheetRepository->getAllSheetsByUserAndEvent($user, $event);

        $enabledSheets = \array_filter($sheets, function (Sheet $sheet) {
            return $sheet->isEnabled();
        });

        // if there is at least one enabled sheet, return only enabled sheets list
        if (\count($enabledSheets) > 0) {
            return $enabledSheets;
        }

        // else return all sheets
        return $sheets;
    }

    /**
     * @param LeniUserViewQuery $query
     *
     * @return null|string
     */
    private function getPreviousLeniUserId(LeniUserViewQuery $query): ?string
    {
        if (!$query->previousExtraData instanceof ExtraData) {
            return null;
        }

        $previousData = unserialize($query->previousExtraData->getValue(), ['allowed_classes' => false]);

        return $previousData[LeniConstants::LENI_COL_USER_ID] ?? null;
    }
}
