<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Command;

use Proximum\Vimeet\Application\Adapter\ThirdParty\Vianeo\VianeoApiCallJobQueueInterface;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet\VianeoExtraDataType;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class VianeoPrepareApiCallHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var VianeoApiCallJobQueueInterface */
    private $vianeoApiCallJobQueue;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param TypeRepositoryInterface           $typeRepository
     * @param SheetRepositoryInterface          $sheetRepository
     * @param VianeoApiCallJobQueueInterface    $vianeoApiCallJobQueue
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        TypeRepositoryInterface $typeRepository,
        SheetRepositoryInterface $sheetRepository,
        VianeoApiCallJobQueueInterface $vianeoApiCallJobQueue
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->typeRepository = $typeRepository;
        $this->sheetRepository = $sheetRepository;
        $this->vianeoApiCallJobQueue = $vianeoApiCallJobQueue;
    }

    /**
     * @param VianeoPrepareApiCall $vianeoPrepareApiCall
     */
    public function handle(VianeoPrepareApiCall $vianeoPrepareApiCall)
    {
        $events = $this->eventRepository->findEventWithParameters(
            [
                Type::TYPE_VIANEO_ENDPOINT,
                Type::TYPE_VIANEO_CONCERNED_TYPES_ID,
            ]
        );

        foreach ($events as $event) {
            $vianeoEndpointParameter = $this->extraParameterRepository->findByEventAndType(
                $event,
                Type::TYPE_VIANEO_ENDPOINT
            );

            $vianeoConcernedTypesIdParameter = $this->extraParameterRepository->findByEventAndType(
                $event,
                Type::TYPE_VIANEO_CONCERNED_TYPES_ID
            );

            if (null === $vianeoEndpointParameter || null === $vianeoConcernedTypesIdParameter) {
                throw new \LogicException(
                    'Can not call VianeoApiCallHandler::handle() if event has not VIANEO_ENDPOINT or VIANEO_CONCERNED_TYPES_ID'
                );
            }

            $types = $this->typeRepository->getByIds(explode(',', $vianeoConcernedTypesIdParameter->getValue()));

            $sheets = $this->sheetRepository->getByTypesAndWithoutGivenExtraData(
                $types,
                VianeoExtraDataType::VIANEO_SHEET_REGISTERED
            );

            foreach ($sheets as $sheet) {
                $this->vianeoApiCallJobQueue->createJob($sheet);
            }
        }
    }
}
