<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter\Api;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Query\Api\Leni\LeniUserViewQuery;
use Proximum\Vimeet\Application\Query\Api\Leni\LeniUserViewQueryHandler;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

class LeniApiHandler
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

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     * @param UserRepositoryInterface           $userRepository
     * @param SheetRepositoryInterface          $sheetRepository
     * @param ParticipantPlanningFormatter      $participantPlanningFormatter
     * @param LeniUserViewQueryHandler          $leniUserViewQueryHandler
     * @param SerializerAdapterInterface        $serializerAdapter
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantPlanningFormatter $participantPlanningFormatter,
        LeniUserViewQueryHandler $leniUserViewQueryHandler,
        SerializerAdapterInterface $serializerAdapter
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->participantPlanningFormatter = $participantPlanningFormatter;
        $this->leniUserViewQueryHandler = $leniUserViewQueryHandler;
        $this->serializerAdapter = $serializerAdapter;
    }

    /**
     * @param LeniApi $command
     */
    public function handle(LeniApi $command): void
    {
        $events = $this->eventRepository->findEventWithLeniApiParameters();

        foreach ($events as $event) {
            $leniUserParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
            $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);

            $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($event);
            $users = $this->userRepository->findByEvent($event);
            $usersExtraData = $this->extraDataRepository->getExtraDataForEventAndName(
                $event,
                ExtraDataType::LENI_FINGERPRINT
            );

            $userFingerPrints = $this->orderExtraData($usersExtraData);

            $leniUserViews = [];

            foreach ($users as $user) {
                $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

                $leniUser = $this->leniUserViewQueryHandler->handle(new LeniUserViewQuery($event, $user, $sheets));
                $leniUserSerialize = $this->serializerAdapter->normalize($leniUser);

                $fingerPrint = md5(implode(',', $leniUserSerialize));

                if (isset($userFingerPrints[$user->getId()])
                    && $fingerPrint !== $userFingerPrints[$user->getId()]->getValue()
                ) {
                }

                $leniUserViews[] = $leniUser;
            }
        }
    }

    /**
     * @param ExtraData[] $usersExtraData
     *
     * @return array
     */
    private function orderExtraData(array $usersExtraData)
    {
        $userFingerPrints = [];

        foreach ($usersExtraData as $userExtraData) {
            $userExtraData[$userExtraData->getUser()->getId()] = $userExtraData;
        }

        return $userFingerPrints;
    }
}
