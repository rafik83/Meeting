<?php

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\Created;
use Proximum\Vimeet\Application\Exception\Happening\SpeakerNotUserException;
use Proximum\Vimeet\Domain\MimeType\MimeType;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        FileStorageInterface $fileStorage,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->fileStorage = $fileStorage;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    public function handle(Create $create): void
    {
        if ($create->isWebinar()) {
            foreach ($create->talkings as $talking) {
                if ($talking["speaker"]->getUser() === null) {
                    throw new SpeakerNotUserException();
                }
            }
        }

        $happening = new Happening(
            $create->event,
            $create->begin,
            $create->end,
            $create->category,
            $create->types,
            $create->questionAllowed,
            $create->limitParticipant,
            $create->invitationCode,
            $create->isWebinar(),
            $create->isInteractiveWebinar(),
            $create->isVideoWebinar(),
            $create->liveUrl,
            $create->sidebarAllowed,
            $create->isWebinar() && $create->webinarRecorded,
            $create->isWebinar() && $create->allowHls,
            $create->isWebinar() && $create->webinarRecorded && $create->webinarRecordSentToSpeakers,
            $create->mustEvaluateHappening
        );

        foreach ($create->translations as $locale => $translation) {
            $webinarHeaderImage = null;
            $webinarWaitingMediaFile = null;
            $webinarWaitingMediaType = null;

            if ($translation['webinarHeaderImage'] instanceof UploadedFile) {
                $webinarHeaderImage = $this->fileStorage->upload($translation['webinarHeaderImage']);
            }

            if ($translation['webinarWaitingMedia'] instanceof UploadedFile) {
                $webinarWaitingMediaType = MimeType::getFormatByMimeType($translation['webinarWaitingMedia']->getMimeType());
                $webinarWaitingMediaFile = $this->fileStorage->upload($translation['webinarWaitingMedia']);
            }

            $happening->setTranslation(
                new Happening\HappeningTranslation(
                    $happening,
                    $locale,
                    $translation['title'],
                    $translation['description'],
                    $webinarHeaderImage,
                    $webinarWaitingMediaFile,
                    $webinarWaitingMediaType
                )
            );
        }

        // Sort speakers by position
        usort($create->talkings, static function (array $one, array $another) {
            return $one['position'] - $another['position'];
        });

        // Set speakers
        $happening->setSpeakers(
            array_map(static function (array $talking) {
                return $talking['speaker'];
            }, $create->talkings)
        );

        $this->happeningRepository->add($happening);

        $this->delayedEventDispatcher->dispatch(
            Events::HAPPENING_CREATED,
            new Created($happening)
        );
    }
}
