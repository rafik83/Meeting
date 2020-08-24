<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Client\WSClient;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

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

    public function __construct(
        WSClient $WSClient,
        TypeRepositoryInterface $typeRepository,
        TemplateDataFactory $templateDataFactory,
        ConvertContactToSheet $convertContactToSheet
    ) {
        $this->WSClient = $WSClient;
        $this->typeRepository = $typeRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->convertContactToSheet = $convertContactToSheet;
    }

    public function handle(Event $event, array $eventConfiguration): void
    {
        $endpoint = $eventConfiguration['endpoint'] ?? null;
        $typeId = $eventConfiguration['type'] ?? null;

        if (null === $endpoint || null === $typeId) {
            return;
        }

        $type = $this->typeRepository->getById($typeId);

        if (!$type instanceof Type) {
            return;
        }

        $contacts = $this->WSClient->getContactsToSynchro($endpoint);

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
