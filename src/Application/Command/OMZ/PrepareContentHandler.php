<?php

namespace Proximum\Vimeet\Application\Command\OMZ;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQueryHandler;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\OMZ\OmzUserListView;
use Proximum\Vimeet\Application\View\OMZ\OmzUserView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class PrepareContentHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var GroupNameResolver */
    private $groupNameResolver;

    /** @var TypeNameResolver */
    private $typeNameResolver;

    /** @var ParticipantPlanningFormatter */
    private $participantPlanningFormatter;

    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var UserInfoGuesser */
    private $userInfoGuesser;

    /** @var QRCodeIdentifierQueryHandler */
    private $QRCodeIdentifierQueryHandler;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        GroupNameResolver $groupNameResolver,
        TypeNameResolver $typeNameResolver,
        UserInfoGuesser $userInfoGuesser,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        QRCodeIdentifierQueryHandler $QRCodeIdentifierQueryHandler,
        SerializerAdapterInterface $serializer
    ) {
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->groupNameResolver = $groupNameResolver;
        $this->typeNameResolver = $typeNameResolver;
        $this->userInfoGuesser = $userInfoGuesser;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->QRCodeIdentifierQueryHandler = $QRCodeIdentifierQueryHandler;
        $this->serializer = $serializer;
    }

    /**
     * @param PrepareContent $prepareContent
     *
     * @return string normalized data for the OMZ import
     */
    public function handle(PrepareContent $prepareContent): string
    {
        $usersViews = [];
        $event      = $prepareContent->event;

        $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($event);

        foreach ($this->userRepository->findWithEnabledSheetByEvent($event) as $user) {
            $userLocale = $event->getAvailableLocale($user->getLocale());
            $userSheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

            $planning = $this->participantPlanningFormatter->formatPlanningFromUserAndEventWithUnallocated(
                $user,
                $event,
                $userLocale
            );

            $userInfo = $this->userInfoGuesser->getUserInfoFromParticipant($user, $userLocale, $userSheets);

            $usersViews[] = new OmzUserView(
                $this->QRCodeIdentifierQueryHandler->handle(new QRCodeIdentifierQuery($event, $user)),
                $this->groupNameResolver->resolve($event, $user, $userSheets),
                null,
                $this->typeNameResolver->resolveWithPreloadedSheets($userSheets, $event->getFallback()),
                $userInfo['gender'],
                $userInfo['firstName'],
                $userInfo['lastName'],
                $userInfo['position'],
                null,
                $userInfo['phone'],
                $user->getEmail(),
                null,
                $userInfo['mobile'],
                $planning
            );
        }

        return $this->serializer->serialize(new OmzUserListView($usersViews), 'csv', [
           'charset' => Charset::WINDOWS_1252,
           'csv_delimiter' => ';',
       ]);
    }
}
