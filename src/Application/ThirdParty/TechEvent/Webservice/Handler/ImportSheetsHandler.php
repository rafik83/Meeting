<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Client\WSClient;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
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

    public function __construct(
        WSClient $WSClient,
        TypeRepositoryInterface $typeRepository,
        TemplateDataFactory $templateDataFactory,
        ConvertContactToSheet $convertContactToSheet,
        LoggerInterface $logger
    ) {
        $this->WSClient = $WSClient;
        $this->typeRepository = $typeRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->convertContactToSheet = $convertContactToSheet;
        $this->logger = $logger;
    }

    public function handle(Event $event, array $eventConfiguration): void
    {
        $endpoint = $eventConfiguration['endpoint'] ?? null;
        $typeId = $eventConfiguration['type'] ?? null;

        // Unique identifier of the contact, can be IDCONTACT, IdContact for eg.
        $identifier = $eventConfiguration['mandatory_keys']['identifier'] ?? null;

        if (null === $endpoint || null === $typeId || null === $identifier) {
            return;
        }

        $type = $this->typeRepository->getById($typeId);

        if (!$type instanceof Type) {
            return;
        }

        $contacts = $this->WSClient->getContactsToSynchro($endpoint, $identifier);

        $this->logger->notice(
            sprintf('VIMEET : Importing %d contacts from techevent on event "%d".', count($contacts), $event->getId())
        );

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType($type, null);
        $sheetTemplate = $this->templateDataFactory->createSheetTemplateFromType($type, null);

        foreach ($contacts as $contact) {
            $this->convertContactToSheet->handle(
                $event,
                $type,
                $registrationTemplate,
                $sheetTemplate,
                $contact,
                $eventConfiguration
            );
        }
    }
}
