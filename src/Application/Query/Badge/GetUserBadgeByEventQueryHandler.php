<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Application\Adapter\QRCodeGeneratorInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class GetUserBadgeByEventQueryHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var QRCodeGeneratorInterface */
    private $qrCodeGenerator;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var CategoryNameResolver */
    private $categoryNameResolver;

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var UserInfoGuesser */
    private $userInfoGuesser;

    public function __construct(
        QueryBusInterface $queryBus,
        QRCodeGeneratorInterface $qrCodeGenerator,
        SheetRepositoryInterface $sheetRepository,
        GroupNameResolver $groupNameResolver,
        CategoryNameResolver $categoryNameResolver,
        TypeNameResolver $typeNameResolver,
        UserInfoGuesser $userInfoGuesser
    ) {
        $this->queryBus = $queryBus;
        $this->qrCodeGenerator = $qrCodeGenerator;
        $this->sheetRepository = $sheetRepository;
        $this->groupNameResolver = $groupNameResolver;
        $this->categoryNameResolver = $categoryNameResolver;
        $this->typeNameResolver = $typeNameResolver;
        $this->userInfoGuesser = $userInfoGuesser;
    }

    public function handle(GetUserBadgeByEventQuery $query): UserBadgeByEventView
    {
        $userSheets = $this->sheetRepository->getSheetsByUserAndEvent($query->user, $query->event);
        $type = $this->typeNameResolver->resolveTypeWithPreloadedSheets($userSheets);

        /** @var Badge $badge */
        $badge = $this->queryBus->handle(new GetBadgeConfigurationByTypeQuery($type));

        if (!$badge->isActivated()) {
            throw new AccessToBadgeDeniedException('Badge for this type is not activated');
        }

        $userInfo = $this->userInfoGuesser->getUserInfoFromParticipant(
            $query->user,
            $query->event->getFallback(),
            $userSheets
        );

        $qrCodeIdentifier = null;
        $qrCodeImageBase64 = null;

        if ($badge->isShowQRCode()) {
            $qrCodeIdentifier = $this->queryBus->handle(new QRCodeIdentifierQuery($query->event, $query->user));
            $qrCodeImageBase64 = $this->qrCodeGenerator->generateBase64Image($qrCodeIdentifier);
        }

        return new UserBadgeByEventView(
            $this->getSheetTitle($badge, $query->user, $userSheets),
            $badge->isShowFirstName() ? $userInfo['firstName'] : null,
            $badge->isShowLastName() ? $userInfo['lastName'] : null,
            $badge->isShowPosition() ? $userInfo['position'] : null,
            $this->getTypeOrCategoryLabel($badge, $userSheets),
            $qrCodeIdentifier,
            $qrCodeImageBase64,
            $this->getHeader($query->event, $badge),
            $badge->getFooterTextColor(),
            $badge->getFooterColor()
        );
    }

    private function getSheetTitle(Badge $badge, User $user, array &$userSheets): ?string
    {
        if (!$badge->isShowSheetTitle()) {
            return null;
        }

        return $this->groupNameResolver->resolve($badge->getEvent(), $user, $userSheets);
    }

    private function getHeader(Event $event, Badge $badge): ?string
    {
        if (!$badge->isShowHeader()) {
            return null;
        }

        if (null !== $badge->getHeader()) {
            return $badge->getHeader();
        }

        return $event->getLocalizedMobileLogo($event->getFallback());
    }

    private function getTypeOrCategoryLabel(Badge $badge, array &$sheets): ?string
    {
        if (!$badge->isShowFooterTypeOrCategory()) {
            return null;
        }

        if ($badge->isShowFooterType()) {
            return $badge->getType()->getTitle($badge->getEvent()->getFallback());
        }

        $category = $this->categoryNameResolver->resolveCategoryForPreloadSheets($sheets);

        if (null === $category) {
            return null;
        }

        return $category->getTitle($badge->getEvent()->getFallback());
    }
}
