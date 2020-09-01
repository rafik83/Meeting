<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedFromApiEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface as UserEventExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConvertToParticipantHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var UserEventExtraDataRepositoryInterface */
    private $userEventExtraDataRepository;

    /** @var UserEventRepositoryInterface */
    private $userEventRepository;

    /** @var SheetAndParticipantTemplateDataHandler */
    private $sheetAndParticipantTemplateDataHandler;

    /** @var Synchronizer */
    private $synchronizer;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        UserEventExtraDataRepositoryInterface $userEventExtraDataRepository,
        UserEventRepositoryInterface $userEventRepository,
        SheetAndParticipantTemplateDataHandler $sheetAndParticipantTemplateDataHandler,
        Synchronizer $synchronizer,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->userEventRepository = $userEventRepository;
        $this->sheetAndParticipantTemplateDataHandler = $sheetAndParticipantTemplateDataHandler;
        $this->synchronizer = $synchronizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime = $dateTime;
    }

    /**
     * @param ConvertToParticipant $convertToParticipant
     *
     * @return null|Participant
     */
    public function handle(ConvertToParticipant $convertToParticipant): ?Participant
    {
        $email = StringHelper::trimSpacesAndNonBreakSpaces($convertToParticipant->email);
        $user = $this->userRepository->findByEmail($email);

        if ($user instanceof User) {
            if (true === $this->ignoreIfExistsUserEventExtraDataForType(
                    $convertToParticipant->event,
                    $user,
                    $convertToParticipant->userEventExtraDataType
                )
            ) {
                return null;
            }
        } else {
            $locale = $convertToParticipant->event->getAvailableLocale($convertToParticipant->locale);
            $user = $this->createUser($email, $locale);
        }

        $participant = $this->createSheetAndParticipant(
            $convertToParticipant->event,
            $convertToParticipant->type,
            $user,
            $convertToParticipant->dataIndexedByTag,
            $convertToParticipant->registrationTemplateData,
            $convertToParticipant->sheetTemplateData,
            $convertToParticipant->sheetState,
            $convertToParticipant->toSetInCatalog
        );

        $this->synchronizer->set($convertToParticipant->registrationTemplateData, $user);

        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($participant->getSheet()));

        return $participant;
    }

    /**
     * @param Event       $event
     * @param User        $user
     * @param null|string $extraDataType
     *
     * @return bool
     */
    private function ignoreIfExistsUserEventExtraDataForType(Event $event, User $user, ?string $extraDataType): bool
    {
        if (null === $extraDataType) {
            return false;
        }

        return null !== $this->userEventExtraDataRepository
                ->getExtraDataForEventNameAndUser($event, $extraDataType, $user);
    }

    /**
     * @param string $email
     * @param string $locale
     *
     * @return User
     */
    private function createUser(string $email, string $locale): User
    {
        $user = new User($email, '', '', $locale);
        $user->setAccount(new User\Account());
        $this->userRepository->add($user);

        return $user;
    }

    private function createSheetAndParticipant(
        Event $event,
        Type $type,
        User $user,
        array $dataIndexedByTag,
        TemplateData $registrationTemplateData,
        TemplateData $sheetTemplateData,
        string $sheetState,
        bool $toSetInCatalog
    ): Participant {
        $sheetAndParticipantTemplateDataView = $this->sheetAndParticipantTemplateDataHandler->handle(
            $dataIndexedByTag,
            $registrationTemplateData,
            $sheetTemplateData
        );

        $sheet = $this->createSheet(
            $event,
            $type,
            $user,
            $sheetAndParticipantTemplateDataView->sheetTitle,
            $sheetAndParticipantTemplateDataView->sheetTemplateData,
            $sheetAndParticipantTemplateDataView->sheetRegistrationData
        );

        $sheet->setState($sheetState);

        if ($toSetInCatalog) {
            $sheet->setInCatalog(true);
            $sheet->setInCatalogAt($this->dateTime);
        }

        $participant = $this->createParticipant(
            $sheet,
            $user,
            $sheetAndParticipantTemplateDataView->participantRegistrationData
        );

        $this->save($sheet, $participant);

        return $participant;
    }

    /**
     * @param Event  $event
     * @param Type   $type
     * @param User   $user
     * @param string $sheetTitle
     * @param array  $sheetTemplateData
     * @param array  $sheetRegistrationData
     *
     * @return Sheet
     */
    private function createSheet(
        Event $event,
        Type $type,
        User $user,
        string $sheetTitle,
        array $sheetTemplateData,
        array $sheetRegistrationData
    ): Sheet {
        $sheet = new Sheet(
            $event,
            $type,
            $sheetTemplateData,
            $user,
            $this->dateTime
        );
        $sheet->setRegistrationData($sheetRegistrationData);
        $sheet->setTitle($sheetTitle);
        $sheet->setImported(true);

        return $sheet;
    }

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     */
    private function save(Sheet $sheet, Participant $participant): void
    {
        $this->sheetRepository->add($sheet);
        $this->participantRepository->add($participant);
        $this->userEventRepository->add(new UserEvent($participant->getUser(), $sheet->getEvent(), $sheet->getType()));
        $this->eventDispatcher->dispatch(
            Events::PARTICIPANT_IMPORTED_FROM_API,
            new ParticipantImportedFromApiEvent($participant)
        );
    }

    private function createParticipant(
        Sheet $sheet,
        User $user,
        array &$participantRegistrationData
    ): Participant {
        $participant = new Participant(
            $sheet,
            $user,
            $participantRegistrationData,
            false,
            $this->dateTime
        );
        $participant->setImported(true);

        return $participant;
    }
}
