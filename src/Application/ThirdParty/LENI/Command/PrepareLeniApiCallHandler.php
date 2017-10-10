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
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\WarningApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Query\LeniUserViewQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Query\LeniUserViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniUserView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
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

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var LeniApiCallHandler */
    private $leniApiCallHandler;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     * @param UserRepositoryInterface           $userRepository
     * @param ParticipantPlanningFormatter      $participantPlanningFormatter
     * @param LeniUserViewQueryHandler          $leniUserViewQueryHandler
     * @param LeniApiCallHandler                $leniApiCallHandler
     * @param SerializerAdapterInterface        $serializerAdapter
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        UserRepositoryInterface $userRepository,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        LeniUserViewQueryHandler $leniUserViewQueryHandler,
        LeniApiCallHandler $leniApiCallHandler,
        SerializerAdapterInterface $serializerAdapter,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->userRepository = $userRepository;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->leniUserViewQueryHandler = $leniUserViewQueryHandler;
        $this->leniApiCallHandler = $leniApiCallHandler;
        $this->serializerAdapter = $serializerAdapter;
        $this->dateTime = $dateTime;
    }

    /**
     * @param PrepareLeniApiCall $command
     *
     * @throws LeniApiServerException
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     */
    public function handle(PrepareLeniApiCall $command): void
    {
        $events = $this->eventRepository->findEventWithLeniApiParameters();

        foreach ($events as $event) {
            if (!$event->hasDay() || $event->getLastDay()->getEndTime() < $this->dateTime) {
                continue;
            }

            $leniUserParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
            $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);

            $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($event);
            $users = $this->userRepository->findByEvent($event);
            $usersExtraData = $this->extraDataRepository->getExtraDataForEventAndName(
                $event,
                ExtraDataType::LENI_FINGERPRINT
            );

            $userFingerPrints = $this->indexExtraDataByUserId($usersExtraData);
            $numberUsersSend = 0;

            foreach ($users as $user) {
                $leniUserView = $this->leniUserViewQueryHandler->handle(new LeniUserViewQuery($event, $user));
                $leniUserSerialize = $this->serializerAdapter->normalize($leniUserView);
                $leniUserView->addSerializeContent($leniUserSerialize);

                $fingerPrint = md5(implode(',', $leniUserSerialize));

                if (isset($userFingerPrints[$user->getId()])
                    && $fingerPrint === $userFingerPrints[$user->getId()]->getValue()
                ) {
                    continue;
                }

                $this->notifyLeniWithUsers($leniUserParameter, $leniEventParameter, $leniUserView);

                if (isset($userFingerPrints[$user->getId()])) {
                    $userFingerPrints[$user->getId()]->update($fingerPrint, $this->dateTime);
                    $this->extraDataRepository->set($userFingerPrints[$user->getId()]);

                    continue;
                }

                $this->extraDataRepository->add(
                    new ExtraData(
                        $user,
                        $event,
                        ExtraDataType::LENI_FINGERPRINT,
                        $fingerPrint,
                        $this->dateTime
                    )
                );

                $numberUsersSend++;
                if ($numberUsersSend === 100) {
                    break;
                }
            }
        }
    }

    /**
     * @param ExtraData[] $usersExtraData
     *
     * @return array
     */
    private function indexExtraDataByUserId(array $usersExtraData)
    {
        $userFingerPrints = [];

        foreach ($usersExtraData as $userExtraData) {
            $userFingerPrints[$userExtraData->getUser()->getId()] = $userExtraData;
        }

        return $userFingerPrints;
    }

    /**
     * @param ExtraParameter $leniUserParameter
     * @param ExtraParameter $leniEventParameter
     * @param LeniUserView   $leniUserView
     *
     * @throws LeniApiServerException
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     */
    private function notifyLeniWithUsers(
        ExtraParameter $leniUserParameter,
        ExtraParameter $leniEventParameter,
        LeniUserView $leniUserView
    ) {
        $this->leniApiCallHandler->handle(new LeniApiCall($leniUserView, $leniUserParameter, $leniEventParameter));
    }
}
