<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFile;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFileHandler;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateCompanyHandler
{
    /** @var Synchronizer */
    private $accountSynchronizer;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var UploadFileHandler */
    private $uploadFileHandler;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param Synchronizer             $accountSynchronizer
     * @param DelayedEventDispatcher   $eventDispatcher
     * @param UploadFileHandler        $uploadFileHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        Synchronizer $accountSynchronizer,
        DelayedEventDispatcher $eventDispatcher,
        UploadFileHandler $uploadFileHandler
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->accountSynchronizer = $accountSynchronizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->uploadFileHandler = $uploadFileHandler;
    }

    public function handle(UpdateCompany $updateCompany): void
    {
        $participant = $updateCompany->participant;
        $sheet = $updateCompany->sheet;
        $companyData = $sheet->getRegistrationData();
        $templateData = $updateCompany->templateData;

        foreach ($updateCompany->data as $key => $value) {
            $templateObject = $templateData->getObject($key);

            if ($templateObject instanceof UploadObject) {
                $companyData = $this
                    ->uploadFileHandler
                    ->handle(
                        new UploadFile(
                            $sheet,
                            $updateCompany->user,
                            $templateObject,
                            $companyData,
                            $templateObject->hasTag(Tag::SHEET_DATA)
                        )
                    )
                ;

                continue;
            }

            if ($templateObject->hasTag(Tag::SHEET_DATA)) {
                $companyData = array_merge($companyData, [$key => $value]);

                // Set the data on the TemplateData to use it with the accountSynchronizer
                $templateData->getObject($key)->setData($value);
            }

            // update sheet title
            if ($templateObject->hasTag(Tag::SHEET_TITLE)
                && $templateObject instanceof ContentObjectInterface
            ) {
                $sheet->setTitle($templateObject->getContentValue());
            }
        }

        $sheet->setRegistrationData($companyData);

        $this->sheetRepository->set($sheet);

        if (null !== $participant && $participant->getUser() === $updateCompany->user) {
            $this->accountSynchronizer->set($templateData, $updateCompany->participant->getUser());
        }

        // Send Sheet Update Event to recalculate completeness of the sheet
        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent);
    }
}
