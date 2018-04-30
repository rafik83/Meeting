<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;

class UpdateHandler
{
    /**
     * @var SpeakerRepositoryInterface
     */
    private $speakerRepository;

    /**
     * @var FileStorageInterface
     */
    private $fileStorageInterface;

    /**
     * UpdateHandler constructor.
     *
     * @param SpeakerRepositoryInterface $speakerRepository
     * @param FileStorageInterface       $fileStorageInterface
     */
    public function __construct(SpeakerRepositoryInterface $speakerRepository, FileStorageInterface $fileStorageInterface)
    {
        $this->speakerRepository    = $speakerRepository;
        $this->fileStorageInterface = $fileStorageInterface;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $logo  = $update->speaker->getLogo();
        $photo = $update->speaker->getPhoto();

        foreach ($update->translations as $locale => $translation) {
            $update->speaker->getTranslations()->get($locale)->update($translation['position']);
        }

        $this->speakerRepository->set($update->speaker->update(
            $update->firstname,
            $update->lastname,
            $update->organization,
            $update->logo ? $this->fileStorageInterface->upload($update->logo) : $logo,
            $update->photo ? $this->fileStorageInterface->upload($update->photo) : $photo
        ));

        if ($logo !== $update->speaker->getLogo()) {
            $this->fileStorageInterface->remove($logo);
        }

        if ($photo !== $update->speaker->getPhoto()) {
            $this->fileStorageInterface->remove($photo);
        }
    }
}
