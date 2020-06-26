<?php

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Exception\Happening\SpeakerNotUserException;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var FileStorageInterface */
    private $fileStorage;

    public function __construct(HappeningRepositoryInterface $happeningRepository, FileStorageInterface $fileStorage)
    {
        $this->happeningRepository = $happeningRepository;
        $this->fileStorage = $fileStorage;
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
            $create->liveUrl,
            $create->isWebinar() && $create->webinarRecorded
        );

        foreach ($create->translations as $locale => $translation) {
            $webinarHeaderImage = null;

            if ($translation['webinarHeaderImage'] instanceof UploadedFile) {
                $webinarHeaderImage = $this->fileStorage->upload($translation['webinarHeaderImage']);
            }

            $happening->setTranslation(
                new Happening\HappeningTranslation(
                    $happening,
                    $locale,
                    $translation['title'],
                    $translation['description'],
                    $webinarHeaderImage
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
    }
}
