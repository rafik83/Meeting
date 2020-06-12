<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\DatesUpdated;
use Proximum\Vimeet\Application\Event\Happening\TypesUpdated;
use Proximum\Vimeet\Application\Exception\Happening\SpeakerNotUserException;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /** @var FileStorageInterface */
    private $fileStorage;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        FileStorageInterface $fileStorage,
        DelayedEventDispatcherInterface $eventDispatcher
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileStorage = $fileStorage;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        if ($update->isWebinar()) {
            foreach ($update->talkings as $talking) {
                if ($talking["speaker"]->getUser() === null) {
                    throw new SpeakerNotUserException();
                }
            }
        }

        $previousTypes = $update->happening->getTypes();
        $previousBegin = $update->happening->getBegin();
        $previousEnd   = $update->happening->getEnd();

        $happening = $update->happening;
        $happening->update(
            $update->begin,
            $update->end,
            $update->category,
            $update->types,
            $update->questionAllowed,
            $update->limitParticipant,
            $update->isWebinar(),
            $update->isInteractiveWebinar(),
            $update->invitationCode
        );

        foreach ($update->translations as $locale => $translation) {
            $currentWebinarHeaderImage = $translation['currentWebinarHeaderImage'];
            $webinarHeaderImage = $currentWebinarHeaderImage;

            if ($translation['webinarHeaderImage'] instanceof UploadedFile) {
                if ($currentWebinarHeaderImage) {
                    $this->fileStorage->remove($currentWebinarHeaderImage);
                }

                $webinarHeaderImage = $this->fileStorage->upload($translation['webinarHeaderImage']);
            }

            $happening->updateTranslation(
                $locale,
                $translation['title'],
                $translation['description'],
                $webinarHeaderImage
            );
        }

        array_walk($update->talkings, function (array &$talking, $key) {
            $talking['position'] += $key;
        });

        // Sort speakers by position
        usort($update->talkings, function (array $one, array $another) { return $one['position'] - $another['position']; });

        // Set speakers
        $happening->setSpeakers(array_map(function (array $talking) { return $talking['speaker']; }, $update->talkings));

        $this->happeningRepository->set($happening);

        if ($previousTypes !== $update->types) {
            $this->eventDispatcher->dispatch(Events::HAPPENING_TYPES_UPDATED, new TypesUpdated($happening));
        }

        if ($previousBegin !== $update->begin || $previousEnd !== $update->end) {
            $this->eventDispatcher->dispatch(Events::HAPPENING_DATES_UPDATED, new DatesUpdated($happening));
        }
    }
}
