<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\ThirdParty\Jenkins\BuildCreatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtml;
use Proximum\Vimeet\Application\Components\Sheet\Pdf\GenerateHtmlFile;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchPdfHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var string */
    private $domain;

    /** @var string */
    private $scheme;

    /** @var GenerateHtml */
    private $generateHtml;

    /** @var GenerateHtmlFile */
    private $generateHtmlFile;

    /** @var BuildCreatorInterface */
    private $buildCreator;

    /** @var string */
    private $jenkinsSheetPdfPrintBuildName;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param GenerateHtmlFile         $generateHtmlFile
     * @param GenerateHtml             $generateHtml
     * @param BuildCreatorInterface    $buildCreator
     * @param string                   $jenkinsSheetPdfPrintBuildName
     * @param string                   $domain
     * @param string                   $scheme
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        SheetRepositoryInterface $sheetRepository,
        GenerateHtmlFile $generateHtmlFile,
        GenerateHtml $generateHtml,
        BuildCreatorInterface $buildCreator,
        string $jenkinsSheetPdfPrintBuildName,
        string $domain,
        string $scheme
    ) {
        $this->eventRepository               = $eventRepository;
        $this->sheetRepository               = $sheetRepository;
        $this->generateHtmlFile              = $generateHtmlFile;
        $this->generateHtml                  = $generateHtml;
        $this->domain                        = $domain;
        $this->scheme                        = $scheme;
        $this->buildCreator                  = $buildCreator;
        $this->jenkinsSheetPdfPrintBuildName = $jenkinsSheetPdfPrintBuildName;
    }

    /**
     * @param BatchPdf $batchPdf
     */
    public function handle(BatchPdf $batchPdf)
    {
        $event  = $this->eventRepository->getById($batchPdf->eventId);

        if (!$event instanceof Event) {
            throw new \DomainException(sprintf('The event %s given does not exist', $batchPdf->eventId));
        }

        $sheets = $this->sheetRepository->getSheetsByIdOrdered($batchPdf->sheetIds, $batchPdf->orderBy);

        $this->generateHtml->setContext($this->scheme, $this->domain);
        $html = $this->generateHtml->printSheets($event, $sheets, $event->getAvailableLocale($batchPdf->locale));
        $htmlFile = $this->generateHtmlFile->generateFile($html);

        $this->buildCreator->create(
            $this->jenkinsSheetPdfPrintBuildName,
            [
                'INPUT'         => $this->generateHtmlFile->getFileDirectory() . $htmlFile->getPath(),
                'OUTPUT'        => $this->generateHtmlFile->getFileDirectory() . $htmlFile->getPath() . '.pdf',
                'EVENTID'       => $event->getId(),
                'EMAIL'         => $batchPdf->emailToNotify,
                'LOCALE'        => $batchPdf->locale,
                'INPUT_FILE_ID' => $htmlFile->getId(),
            ]
        );
    }
}
