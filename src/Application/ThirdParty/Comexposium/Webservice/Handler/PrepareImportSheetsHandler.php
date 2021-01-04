<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\Adapter\ThirdParty\Comexposium\ComexposiumJobQueueInterface;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class PrepareImportSheetsHandler
{
    private const CHUNK_SIZE = 100;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var ComexposiumWebservice */
    private $comexposiumWebservice;

    /** @var ComexposiumJobQueueInterface */
    private $comexposiumJobQueue;

    /** @var RemoveAlreadyImportedReferences */
    private $removeAlreadyImportedReferences;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ComexposiumWebservice $comexposiumWebservice,
        ComexposiumJobQueueInterface $comexposiumJobQueue,
        RemoveAlreadyImportedReferences $removeAlreadyImportedReferences
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->comexposiumWebservice = $comexposiumWebservice;
        $this->comexposiumJobQueue = $comexposiumJobQueue;
        $this->removeAlreadyImportedReferences = $removeAlreadyImportedReferences;
    }

    public function handle(): void
    {
        $events = $this->eventRepository->findEventWithParameters([Type::TYPE_COMEXPOSIUM_EVENT_REFERENCE]);

        foreach ($events as $event) {
            $eventReferenceExtraParameter = $this->extraParameterRepository->findByEventAndType(
                $event,
                Type::TYPE_COMEXPOSIUM_EVENT_REFERENCE
            );

            if (!$eventReferenceExtraParameter instanceof ExtraParameter) {
                continue;
            }

            $registrationReferences = $this->comexposiumWebservice->getRegistrationsReference(
                $eventReferenceExtraParameter->getValue()
            );

            $registrationReferences = $this->removeAlreadyImportedReferences->handle($event, $registrationReferences);

            if (empty($registrationReferences)) {
                continue;
            }

            foreach (\array_chunk($registrationReferences, self::CHUNK_SIZE, false) as $registrationReferencesChunk) {
                $this->comexposiumJobQueue->getRegistrations($event, $registrationReferencesChunk);
            }
        }
    }
}
