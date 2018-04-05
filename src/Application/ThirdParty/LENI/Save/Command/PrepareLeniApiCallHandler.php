<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command;

use Proximum\Vimeet\Application\Adapter\ThirdParty\LENI\Save\LeniApiCallJobQueueInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Normalizer\LeniUserViewNormalizer;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQueryHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
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

    /** @var LeniUserViewQueryHandler */
    private $leniUserViewQueryHandler;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var LeniApiCallJobQueueInterface */
    private $leniApiCallJobQueue;

    /** @var LeniUserViewNormalizer */
    private $leniUserViewNormalizer;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     * @param UserRepositoryInterface           $userRepository
     * @param ParticipantPlanningFormatter      $participantPlanningFormatter
     * @param LeniUserViewQueryHandler          $leniUserViewQueryHandler
     * @param LeniUserViewNormalizer            $leniUserViewNormalizer
     * @param LeniApiCallJobQueueInterface      $leniApiCallJobQueue
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        UserRepositoryInterface $userRepository,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        LeniUserViewQueryHandler $leniUserViewQueryHandler,
        LeniUserViewNormalizer $leniUserViewNormalizer,
        LeniApiCallJobQueueInterface $leniApiCallJobQueue,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->userRepository = $userRepository;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->leniUserViewQueryHandler = $leniUserViewQueryHandler;
        $this->leniApiCallJobQueue = $leniApiCallJobQueue;
        $this->leniUserViewNormalizer = $leniUserViewNormalizer;
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

        $saveModeEnabled = $leniModeParameter !== null
            && \in_array(
                $leniModeParameter->getValue(),
                [
                    Type::VALUE_LENI_MODE_SAVE,
                    Type::VALUE_LENI_MODE_BOTH,
                ],
                true
            );

        if (!$saveModeEnabled || null === $leniUserParameter || null === $leniEventParameter) {
            throw new \LogicException(
                'Can not call PrepareLeniApiCallHandler if send mode is not enabled or event has not LENI_USER and LENI_EVENT'
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
            $this->handleUser($event, $user, $usersExtraData[$user->getId()] ?? null);
        }

        $this->participantPlanningFormatter->resetPlanningHandlerForEvent($event);
    }

    /**
     * @param Event          $event
     * @param User           $user
     * @param null|ExtraData $previousUserEventExtraData
     */
    private function handleUser(Event $event, User $user, ?ExtraData $previousUserEventExtraData): void
    {
        try {
            $leniUserView = $this->leniUserViewQueryHandler->handle(
                new LeniUserViewQuery($event, $user, $previousUserEventExtraData)
            );
        } catch (SheetNotFoundException $sheetNotFoundException) {
            return;
        }

        $leniUserData = $this->leniUserViewNormalizer->normalize($leniUserView);

        // User data did not changed, skip
        if ($previousUserEventExtraData instanceof ExtraData
            && $leniUserData ===  unserialize($previousUserEventExtraData->getValue(), ['allowed_classes' => false])
        ) {
            return;
        }

        $userExtraDataPendingFingerprint = $this->addOrUpdatePendingFingerprint($event, $user, $leniUserData);

        // Create a job for calling LENI API
        $this->leniApiCallJobQueue->createJob($userExtraDataPendingFingerprint);
    }

    /**
     * @param Event $event
     * @param User  $user
     * @param array $leniUserData
     *
     * @return ExtraData
     */
    private function addOrUpdatePendingFingerprint(Event $event, User $user, array &$leniUserData): ExtraData
    {
        $userExtraDataPendingFingerprint = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $event,
            ExtraDataType::LENI_FINGERPRINT_PENDING,
            $user
        );

        $fingerPrint = serialize($leniUserData);

        if ($userExtraDataPendingFingerprint instanceof ExtraData) {
            $userExtraDataPendingFingerprint->update($fingerPrint, $this->dateTime);
            $this->extraDataRepository->set($userExtraDataPendingFingerprint);
        } else {
            $userExtraDataPendingFingerprint = new ExtraData(
                $user,
                $event,
                ExtraDataType::LENI_FINGERPRINT_PENDING,
                $fingerPrint,
                $this->dateTime
            );
            $this->extraDataRepository->add($userExtraDataPendingFingerprint);
        }

        return $userExtraDataPendingFingerprint;
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
