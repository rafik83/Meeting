<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Repository\Happening\SpeakerRepositoryInterface;

class DeleteHandler
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
     * DeleteHandler constructor.
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
     * @param Delete $delete
     */
    public function handle(Delete $delete)
    {
        $logo  = $delete->speaker->getLogo();
        $photo = $delete->speaker->getPhoto();

        $this->speakerRepository->remove($delete->speaker);
        $this->fileStorageInterface->remove($logo);
        $this->fileStorageInterface->remove($photo);
    }
}
