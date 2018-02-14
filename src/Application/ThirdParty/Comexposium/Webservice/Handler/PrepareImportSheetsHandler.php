<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\Adapter\ThirdParty\Comexposium\ComexposiumJobQueueInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class PrepareImportSheetsHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    /** @var ComexposiumJobQueueInterface */
    private $comexposiumJobQueue;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ComexposiumWebservice $comexposiumWebservice,
        ComexposiumJobQueueInterface $comexposiumJobQueue
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->comexposiumWebservice = $comexposiumWebservice;
        $this->comexposiumJobQueue = $comexposiumJobQueue;
    }

    public function handle()
    {

    }
}
