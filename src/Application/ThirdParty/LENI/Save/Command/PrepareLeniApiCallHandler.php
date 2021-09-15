<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command;

use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

/**
 * Prepare LENI EXHIBIS Api call handler
 */
class PrepareLeniApiCallHandler
{
    /** @var ParticipantPlanningFormatter */
    private $participantPlanningFormatter;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var PrepareUserDataForApiCallHandler */
    private $prepareUserDataForApiCallHandler;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     * @param UserRepositoryInterface           $userRepository
     * @param ParticipantPlanningFormatter      $participantPlanningFormatter
     * @param PrepareUserDataForApiCallHandler  $prepareUserDataForApiCallHandler
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        UserRepositoryInterface $userRepository,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        PrepareUserDataForApiCallHandler $prepareUserDataForApiCallHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->userRepository = $userRepository;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->prepareUserDataForApiCallHandler = $prepareUserDataForApiCallHandler;
        $this->dateTime = $dateTime;
    }

    /**
     * @param PrepareLeniApiCall $command
     *
     * @throws \LogicException
     * @throws DayNotDefinedException
     */
    public function handle(PrepareLeniApiCall $command): void
    {
        $events = $this->eventRepository->findEventWithParameters([Type::TYPE_LENI_USER, Type::TYPE_LENI_EVENT]);

        foreach ($events as $event) {
            $this->handleEvent($event);
        }
    }

    /**
     * @param Event $event
     *
     * @throws \LogicException
     * @throws DayNotDefinedException
     */
    private function handleEvent(Event $event): void
    {
        if (!$event->hasDay() || $event->isFinished($this->dateTime)) {
            return;
        }

        $leniUserParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);
        $leniModeParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_MODE);

        $saveModeEnabled = null !== $leniModeParameter
            && \in_array(
                $leniModeParameter->getValue(),
                [
                    Type::VALUE_LENI_MODE_SAVE,
                    Type::VALUE_LENI_MODE_BOTH,
                ],
                true
            );

        if (!$saveModeEnabled) {
            return;
        }

        if (null === $leniUserParameter || null === $leniEventParameter) {
            throw new \LogicException(
                sprintf(
                    'Can not call PrepareLeniApiCallHandler if event has not LENI_USER and LENI_EVENT for event %d',
                    $event->getId()
                )
            );
        }

        $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($event);
        $users = $this->userRepository->findWithSheetByEvent($event);
        $usersExtraData = $this->extraDataRepository->getExtraDataForEventAndName(
            $event,
            ExtraDataType::LENI_FINGERPRINT
        );

        $usersExtraData = $this->indexExtraDataByUserId($usersExtraData);

        foreach ($users as $user) {
            $this->prepareUserDataForApiCallHandler->handle(
                new PrepareUserDataForApiCall($event, $user, $usersExtraData[$user->getId()] ?? null)
            );
        }

        $this->participantPlanningFormatter->resetPlanningHandlerForEvent($event);
    }


    /**
     * @param ExtraData[] $usersExtraData
     *
     * @return array
     */
    private function indexExtraDataByUserId(array &$usersExtraData): array
    {
        $userFingerPrints = [];

        foreach ($usersExtraData as $userExtraData) {
            $userFingerPrints[$userExtraData->getUser()->getId()] = $userExtraData;
        }

        return $userFingerPrints;
    }
}
