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
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
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

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var UserInfoGuesser */
    private $userInfoGuesser;

    public function __construct(
        QueryBusInterface $queryBus,
        QRCodeGeneratorInterface $qrCodeGenerator,
        SheetRepositoryInterface $sheetRepository,
        GroupNameResolver $groupNameResolver,
        TypeNameResolver $typeNameResolver,
        UserInfoGuesser $userInfoGuesser
    ) {
        $this->queryBus = $queryBus;
        $this->qrCodeGenerator = $qrCodeGenerator;
        $this->sheetRepository = $sheetRepository;
        $this->groupNameResolver = $groupNameResolver;
        $this->typeNameResolver = $typeNameResolver;
        $this->userInfoGuesser = $userInfoGuesser;
    }

    public function handle(GetUserBadgeByEventQuery $query): UserBadgeByEventView
    {
        $eventLocaleFallback = $query->event->getFallback();
        $userSheets = $this->sheetRepository->getSheetsByUserAndEvent($query->user, $query->event);
        $userInfo = $this->userInfoGuesser->getUserInfoFromParticipant($query->user, $eventLocaleFallback, $userSheets);
        $qrCodeIdentifier = $this->queryBus->handle(new QRCodeIdentifierQuery($query->event, $query->user));

        return new UserBadgeByEventView(
            $this->groupNameResolver->resolve($query->event, $query->user, $userSheets),
            $userInfo['firstName'],
            $userInfo['lastName'],
            $userInfo['position'],
            $this->typeNameResolver->resolveWithPreloadedSheets($userSheets, $eventLocaleFallback),
            $qrCodeIdentifier,
            $this->qrCodeGenerator->generateBase64Image($qrCodeIdentifier)
        );
    }
}
