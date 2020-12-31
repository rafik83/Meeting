<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFile;
use Proximum\Vimeet\Application\Command\Participant\Upload\UploadFileHandler;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantUpdatedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetTitleCheckEvent;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateProfileHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var Synchronizer */
    private $accountSynchronizer;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var UploadFileHandler */
    private $uploadFileHandler;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        Synchronizer $accountSynchronizer,
        DelayedEventDispatcher $eventDispatcher,
        UploadFileHandler $uploadFileHandler
    ) {
        $this->participantRepository = $participantRepository;
        $this->accountSynchronizer = $accountSynchronizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->uploadFileHandler = $uploadFileHandler;
    }

    public function handle(UpdateProfile $updateProfile): void
    {
        $participant = $updateProfile->participant;
        $participantData = $updateProfile->participant->getData();
        $templateData = $updateProfile->templateData;

        foreach ($updateProfile->data as $key => $value) {
            $templateObject = $templateData->getObject($key);

            if ($templateObject instanceof UploadObject) {
                $participantData = $this
                    ->uploadFileHandler
                    ->handle(
                        new UploadFile(
                            $participant->getSheet(),
                            $participant->getUser(),
                            $templateObject,
                            $participantData,
                            $templateObject->hasTag(Tag::SHEET_DATA)
                        )
                    )
                ;

                continue;
            }

            if ($templateObject->hasTag(Tag::PARTICIPANT_DATA)) {
                $participantData = array_merge($participantData, [$key => $value]);

                // Set the data on the TemplateData to use it with the accountSynchronizer
                $templateObject->setData($value);
            }
        }

        $updateProfile->participant->setData($participantData);

        $this->participantRepository->set($participant);

        if ($participant->getUser() === $updateProfile->user) {
            $this->accountSynchronizer->set($templateData, $updateProfile->participant->getUser());
        }

        // Send event to check and update sheet title depends on sheet title or owner fullname settings
        $sheetTitleCheckEvent = new SheetTitleCheckEvent($participant->getSheet());

        $this->eventDispatcher->dispatch(Events::PARTICIPANT_UPDATED, new ParticipantUpdatedEvent($participant, $templateData));
        $this->eventDispatcher->dispatch(Events::SHEET_TITLE_CHECK, $sheetTitleCheckEvent);
    }
}
