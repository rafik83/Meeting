<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Command;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ThirdParty\LENI\LeniApiCallJobQueueInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Query\LeniUserViewQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Query\LeniUserViewQueryHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
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

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var LeniApiCallJobQueueInterface */
    private $leniApiCallJobQueue;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     * @param UserRepositoryInterface           $userRepository
     * @param ParticipantPlanningFormatter      $participantPlanningFormatter
     * @param LeniUserViewQueryHandler          $leniUserViewQueryHandler
     * @param SerializerAdapterInterface        $serializerAdapter
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
        SerializerAdapterInterface $serializerAdapter,
        LeniApiCallJobQueueInterface $leniApiCallJobQueue,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->userRepository = $userRepository;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->leniUserViewQueryHandler = $leniUserViewQueryHandler;
        $this->serializerAdapter = $serializerAdapter;
        $this->leniApiCallJobQueue = $leniApiCallJobQueue;
        $this->dateTime = $dateTime;
    }

    /**
     * @param PrepareLeniApiCall $command
     *
     * @throws LeniApiServerException
     * @throws NotValidApiCallException
     */
    public function handle(PrepareLeniApiCall $command): void
    {
        $events = $this->eventRepository->findEventWithParameters([Type::TYPE_LENI_USER, Type::TYPE_LENI_EVENT]);

        foreach ($events as $event) {
            if (!$event->hasDay() || $event->getLastDay()->getEndTime() < $this->dateTime) {
                continue;
            }

            $leniUserParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
            $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);

            if (null === $leniUserParameter || null === $leniEventParameter) {
                throw new \LogicException(
                    'Can not call PrepareLeniApiCallHandler if event has not LENI_USER and LENI_EVENT'
                );
            }

            $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($event);
            $users = $this->userRepository->findByEvent($event);
            $usersExtraData = $this->extraDataRepository->getExtraDataForEventAndName(
                $event,
                ExtraDataType::LENI_FINGERPRINT
            );

            $usersExtraData = $this->indexExtraDataByUserId($usersExtraData);

            foreach ($users as $user) {
                $previousUserExtraData = null;

                if (isset($usersExtraData[$user->getId()])) {
                    $previousUserExtraData = $usersExtraData[$user->getId()];
                }

                try {
                    $leniUserView = $this->leniUserViewQueryHandler->handle(new LeniUserViewQuery($event, $user));
                } catch (SheetNotFoundException $sheetNotFoundException) {
                    continue;
                }

                $leniUserData = $this->serializerAdapter->normalize(
                    $leniUserView,
                    null,
                    ['previousUserExtraData' => $previousUserExtraData]
                );
                $fingerPrint = serialize($leniUserData);

                // User data did not changed, skip
                if ($previousUserExtraData instanceof ExtraData
                    && $fingerPrint === $previousUserExtraData->getValue()
                ) {
                    continue;
                }

                $userExtraDataPendingFingerprint = $this->addOrUpdatePendingFingerprint($event, $user, $fingerPrint);

                // Create a job for calling LENI API
                $this->leniApiCallJobQueue->createJob($userExtraDataPendingFingerprint);
            }
        }
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $fingerPrint
     *
     * @return ExtraData
     */
    private function addOrUpdatePendingFingerprint(Event $event, User $user, string $fingerPrint): ExtraData
    {
        $userExtraDataPendingFingerprint = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $event,
            ExtraDataType::LENI_FINGERPRINT_PENDING,
            $user
        );

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
    private function indexExtraDataByUserId(array $usersExtraData): array
    {
        $userFingerPrints = [];

        foreach ($usersExtraData as $userExtraData) {
            $userFingerPrints[$userExtraData->getUser()->getId()] = $userExtraData;
        }

        return $userFingerPrints;
    }
}
