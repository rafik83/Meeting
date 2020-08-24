<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class PrepareImportSheetsHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var ImportSheetsHandler */
    private $importSheetsHandler;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ImportSheetsHandler $importSheetsHandler
    ) {
        $this->eventRepository = $eventRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->importSheetsHandler = $importSheetsHandler;
    }

    public function handle(): void
    {
        $events = $this->eventRepository->findEventWithParameters([Type::TYPE_TECH_EVENT_CONFIGURATION]);

        foreach ($events as $event) {
            $eventConfigurationParameter = $this->extraParameterRepository->findByEventAndType(
                $event,
                Type::TYPE_TECH_EVENT_CONFIGURATION
            );

            if (!$eventConfigurationParameter instanceof ExtraParameter) {
                continue;
            }

            $eventConfiguration = json_decode($eventConfigurationParameter->getValue(), true);

            if ($eventConfiguration === false || !\is_array($eventConfiguration)) {
                continue;
            }

            $this->importSheetsHandler->handle($event, $eventConfiguration);
        }
    }
}
