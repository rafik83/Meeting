<?php

namespace Proximum\Vimeet\Domain\UserEventView;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Event\Filter\GetTemplateFiltersQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEvent\UserEventViewRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User as UserRepository;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class UserEventViewsFactory
{
    /** @var UserEventViewRepositoryInterface */
    private $userEventViewRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var UserRepository\FormDataRepositoryInterface */
    private $formDataRepositoryInterface;

    /** @var bool */
    private $isEventDataPreloaded = false;

    /** @var ExtraData[] indexed by User id */
    private $preloadedExtraDataVisioIndexedByUserId = [];

    /** @var ExtraData[] indexed by User id */
    private $preloadedExtraDataVisioTestedIndexedByUserId = [];

    public function __construct(
        UserEventViewRepositoryInterface $userEventViewRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        QueryBusInterface $queryBus,
        UserRepository\FormDataRepositoryInterface $formDataRepositoryInterface
    ) {
        $this->userEventViewRepository = $userEventViewRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->queryBus = $queryBus;
        $this->formDataRepositoryInterface = $formDataRepositoryInterface;
    }

    /**
     * @return UserEventView[]
     */
    public function getByEvent(Event $event): array
    {
        $this->preloadEvent($event);

        return $this->getUserEventViews($event, $this->userEventViewRepository->getByEvent($event));
    }

    /**
     * @return UserEventView[]
     */
    public function getByEventAndUser(Event $event, User $user): array
    {
        return $this->getUserEventViews(
            $event,
            $this->userEventViewRepository->getAllSheetsByUserAndEvent($user, $event),
            $user
        );
    }

    /**
     * @return UserEventView[]
     */
    private function getUserEventViews(Event $event, array $results, ?User $filteredUser = null): array
    {
        $userEventViews = [];
        $templateFilters = $this->queryBus->handle(
            new GetTemplateFiltersQuery($event, Tag::PARTICIPANT_DATA)
        );

        foreach ($results as $result) {
            if (null === $filteredUser || $filteredUser->getId() === $result['ownerId']) {
                $this->addUserEventViewOrSheetToExistingOne(
                    $userEventViews,
                    $event->getId(),
                    $result['ownerId'],
                    $result['ownerFirstName'],
                    $result['ownerLastName'],
                    $result['ownerEmail'],
                    $result['ownerLocale'],
                    $result['sheetObject'],
                    $templateFilters
                );
            }

            if (null === $filteredUser || $filteredUser->getId() === $result['userId']) {
                $this->addUserEventViewOrSheetToExistingOne(
                    $userEventViews,
                    $event->getId(),
                    $result['userId'],
                    $result['userFirstName'],
                    $result['userLastName'],
                    $result['userEmail'],
                    $result['userLocale'],
                    $result['sheetObject'],
                    $templateFilters
                );
            }
        }

        return $userEventViews;
    }

    /**
     * @param UserEventView[] $userEventViews
     */
    private function addUserEventViewOrSheetToExistingOne(
        array &$userEventViews,
        int $eventId,
        int $userId,
        ?string $firstName,
        ?string $lastName,
        string $email,
        string $locale,
        Sheet $sheet,
        array $templateFilters
    ): void {
        if (isset($userEventViews[$userId])) {
            if (!$userEventViews[$userId]->hasSheetId($sheet->getId())) {
                $userEventViews[$userId]->addSheet(['id' => $sheet->getId()]);
            }

            return;
        }

        $dataMappedToTemplateFilters = $this->getDataMappedToTemplateFilters($sheet, $eventId, $userId, $templateFilters);

        $userEventViews[$userId] = new UserEventView(
            $eventId,
            $userId,
            $firstName,
            $lastName,
            $email,
            $locale,
            $this->isVisio($eventId, $userId),
            $this->isVisioTested($eventId, $userId),
            [
                ['id' => $sheet->getId()],
            ],
            $dataMappedToTemplateFilters
        );
    }

    private function getDataMappedToTemplateFilters(
        Sheet $sheet,
        int $eventId,
        int $userId,
        array &$templateFilters
    ): array {
        $formData = $this->formDataRepositoryInterface->getDataByEventIdAndUserId($eventId, $userId);

        foreach ($sheet->getParticipants() as $participant) {
            if (empty($participant->getData())) {
                continue;
            }

            $formData[] = $participant->getData();
        }

        if (empty($formData)) {
            return [];
        }

        return TemplateObjectFilterTransformer::transform($templateFilters, array_merge(...$formData));
    }

    private function isVisio(int $eventId, int $userId): bool
    {
        if ($this->isEventDataPreloaded) {
            return isset($this->preloadedExtraDataVisioIndexedByUserId[$userId]);
        }

        return $this->hasUserEventExtraData($eventId, $userId, Type::IS_PARTICIPANT_VISIO);
    }

    private function isVisioTested(int $eventId, int $userId): bool
    {
        if ($this->isEventDataPreloaded) {
            return isset($this->preloadedExtraDataVisioTestedIndexedByUserId[$userId]);
        }

        return $this->hasUserEventExtraData($eventId, $userId, Type::VISIO_TESTED);
    }

    private function hasUserEventExtraData(int $eventId, int $userId, string $name): bool
    {
        return null !== $this->extraDataRepository->getExtraDataForEventIdNameAndUserId($eventId, $name, $userId);
    }

    private function preloadEvent(Event $event): void
    {
        $this->preloadEventUsersIsVisio($event);
        $this->preloadEventUsersIsVisioTested($event);
        $this->isEventDataPreloaded = true;
    }

    private function preloadEventUsersIsVisio(Event $event): void
    {
        $this->preloadedExtraDataVisioIndexedByUserId = $this->extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId($event->getId(), Type::IS_PARTICIPANT_VISIO);
    }

    private function preloadEventUsersIsVisioTested(Event $event): void
    {
        $this->preloadedExtraDataVisioTestedIndexedByUserId = $this->extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId($event->getId(), Type::VISIO_TESTED);
    }
}
