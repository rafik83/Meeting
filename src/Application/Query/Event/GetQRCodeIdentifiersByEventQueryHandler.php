<?php

namespace Proximum\Vimeet\Application\Query\Event;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierListView;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierView;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class GetQRCodeIdentifiersByEventQueryHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var UserInfoGuesser */
    private $userInfoGuesser;

    /** @var ScanRepositoryInterface */
    private $scanRepository;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        QueryBusInterface $queryBus,
        SheetRepositoryInterface $sheetRepository,
        UserInfoGuesser $userInfoGuesser,
        ScanRepositoryInterface $scanRepository,
        GroupNameResolver $groupNameResolver,
        TypeNameResolver $typeNameResolver,
        \DateTimeInterface $dateTime,
        RouterInterface $router
    ) {
        $this->queryBus = $queryBus;
        $this->sheetRepository = $sheetRepository;
        $this->userInfoGuesser = $userInfoGuesser;
        $this->scanRepository = $scanRepository;
        $this->groupNameResolver = $groupNameResolver;
        $this->typeNameResolver = $typeNameResolver;
        $this->dateTime = $dateTime;
        $this->router = $router;
    }

    public function handle(GetQRCodeIdentifiersByEventQuery $query): QRCodeIdentifierListView
    {
        $sheets = $this->sheetRepository->getSheetsEnabledByEvent($query->event);
        $users = [];
        $userSheets = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet->getParticipantsArray() as $participant) {
                $user = $participant->getUser();
                $userId = $user->getId();
                $users[$userId] = $user;
                $userSheets[$userId][] = $sheet;
            }
        }

        $scansIndexedByUserId = $query->getPreviousScan ?
            $this->scanRepository->getScanDateByUsersAndEvent(
                $users,
                $query->event,
                $this->dateTime
            )
            : []
        ;

        $qrCodePayloadListView = [];

        foreach ($users as $user) {
            $userId = $user->getId();
            $currentUserSheets = $userSheets[$userId];
            $userLocale = $query->event->getAvailableLocale($user->getLocale());
            $userInfo = $this->userInfoGuesser->getUserInfoFromParticipant(
                $user,
                $userLocale,
                $currentUserSheets
            );

            try {
                $qrCodePayloadListView[] = new QRCodeIdentifierView(
                    $this->queryBus->handle(new QRCodeIdentifierQuery($query->event, $user)),
                    $userInfo['firstName'],
                    $userInfo['lastName'],
                    $this->groupNameResolver->resolve($query->event, $user, $currentUserSheets),
                    $this->typeNameResolver->resolveWithPreloadedSheets($currentUserSheets, $query->locale),
                    isset($scansIndexedByUserId[$userId]) ? $scansIndexedByUserId[$userId]->getScannedAt() : null,
                    $this->router->generate(
                        'admin_user_event_badge',
                        [
                            'user' => $userId,
                            'event' => $query->event->getId(),
                        ]
                    ),
                    $this->router->generate(
                        'admin_user_event_planning',
                        [
                            'user' => $userId,
                            'event' => $query->event->getId(),
                        ]
                    )
                );
            } catch (SheetNotFoundException $sheetNotFoundException) {
                continue;
            }
        }

        return new QRCodeIdentifierListView($qrCodePayloadListView);
    }
}
