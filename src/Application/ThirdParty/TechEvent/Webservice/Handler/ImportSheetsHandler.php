<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Client\WSClient;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Configuration\MappingConfigurationChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Psr\Log\LoggerInterface;

class ImportSheetsHandler
{
    /** @var WSClient */
    private $WSClient;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var ConvertContactToSheet */
    private $convertContactToSheet;

    /** @var LoggerInterface */
    private $logger;

    /** @var MappingConfigurationChecker */
    private $mappingConfigurationChecker;

    public function __construct(
        WSClient $WSClient,
        TypeRepositoryInterface $typeRepository,
        MappingConfigurationChecker $mappingConfigurationChecker,
        TemplateDataFactory $templateDataFactory,
        ConvertContactToSheet $convertContactToSheet,
        LoggerInterface $logger
    ) {
        $this->WSClient = $WSClient;
        $this->typeRepository = $typeRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->convertContactToSheet = $convertContactToSheet;
        $this->logger = $logger;
        $this->mappingConfigurationChecker = $mappingConfigurationChecker;
    }

    public function handle(Event $event, array $eventConfiguration): void
    {
        $endpoint = $eventConfiguration['endpoint'] ?? null;

        if (!$this->mappingConfigurationChecker->isMappingConfigurationValid($eventConfiguration)) {
            $this->logger->warning(
                sprintf(
                    'VIMEET - The mapping for the event %d is not valid : %s',
                    $event->getId(),
                    json_encode($eventConfiguration)
                )
            );

            return;
        }

        // Unique identifier of the contact, can be IDCONTACT, IdContact for eg.
        $identifier = $eventConfiguration['mandatory_keys']['identifier'] ?? null;

        if (null === $identifier) {
            return;
        }

        $types = $this->typeRepository->getTypesByEvent($event);

        if (empty($types)) {
            return;
        }

        $contacts = $this->WSClient->getContactsToSynchro($endpoint, $identifier);

        $this->logger->notice(
            sprintf('VIMEET : Importing %d contacts from techevent on event "%d".', count($contacts), $event->getId())
        );

        $registrationTemplates = [];
        $sheetTemplates = [];

        // index templates by type id.
        foreach ($types as $type) {
            $registrationTemplates[$type->getId()] = $this->templateDataFactory->createRegistrationFromType($type, null);
            $sheetTemplates[$type->getId()] = $this->templateDataFactory->createSheetTemplateFromType($type, null);
        }

        foreach ($contacts as $contact) {
            $this->convertContactToSheet->handle(
                $event,
                $types,
                $registrationTemplates,
                $sheetTemplates,
                $contact,
                $eventConfiguration
            );
        }
    }
}
