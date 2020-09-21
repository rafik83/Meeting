<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ButtonLink;
use Proximum\Vimeet\Domain\Template\TemplateObject\MediaCollection;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\Url;
use Psr\Log\LoggerInterface;

class TemplateObjectUrlQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        EventUrlGeneratorInterface $eventUrlGenerator,
        LoggerInterface $logger
    )
    {
        $this->templateDataFactory = $templateDataFactory;
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->logger = $logger;
    }

    public function handle(TemplateObjectUrlQuery $query): string
    {
        $presentationData = $this->templateDataFactory->createFromSheet($query->sheet, $query->locale);
        $registrationData = $this->templateDataFactory->createRegistrationFromSheet($query->sheet, $query->locale);
        $sheetData = array_merge($presentationData->getObjects(), $registrationData->getObjects());

        if (!isset($sheetData[$query->objectId])) {
            $this->logger->error(sprintf('ObjectId %s not found in sheet #%d data', $query->objectId, $query->sheet->getId()));
            throw new \RunTimeException('Url to redirect to not found');
        }

        if ($sheetData[$query->objectId] instanceof Url) {
            return $sheetData[$query->objectId]->getUrl();
        }
        if ($sheetData[$query->objectId] instanceof ButtonLink) {
            return $sheetData[$query->objectId]->getUrl();
        }
        if ($sheetData[$query->objectId] instanceof MediaCollection && null !== $query->index) {
            return $sheetData[$query->objectId]->getMedias()[$query->index]->url;
        }
        if ($sheetData[$query->objectId] instanceof MultiUploadCollectionObject && null !== $query->index) {
            return $this->eventUrlGenerator->generateEventAbsoluteUrl($query->event, 'event_sheet_show_uploaded_file', [
                'sheetToDisplayId' => $query->sheet->getId(),
                'objectKey' => $query->objectId,
                'path' => $sheetData[$query->objectId]->getUploads()[$query->index]->getPath(),
            ]);
        }

        $this->logger->error(sprintf('ObjectId %s in sheet #%d registration data has not support for redirection', $query->objectId, $query->sheet->getId()));
        throw new \RuntimeException('Unsupported type from sheet data object');
    }
}
