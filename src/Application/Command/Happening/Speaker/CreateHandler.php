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
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Happening\SpeakerTranslation;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;

class CreateHandler
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
     * CreateHandler constructor.
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
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $speaker = new Speaker(
            $create->event,
            $create->firstname,
            $create->lastname,
            $create->organization,
            $this->fileStorageInterface->upload($create->logo),
            $this->fileStorageInterface->upload($create->photo)
        );

        foreach ($create->translations as $locale => $translation) {
            $speaker->getTranslations()->set(
                $locale,
                new SpeakerTranslation($speaker, $locale, $translation['position'])
            );
        }

        $this->speakerRepository->add($speaker);
    }
}
