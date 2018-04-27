<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Happening\SpeakerTranslation;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Update
{
    /**
     * @var Speaker
     */
    public $speaker;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $function;

    /**
     * @var string
     */
    public $organization;

    /**
     * @var UploadedFile
     */
    public $logo;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var UploadedFile
     */
    public $photo;

    /**
     * Create constructor.
     *
     * @param Speaker $speaker
     */
    public function __construct(Speaker $speaker)
    {
        $this->speaker      = $speaker;
        $this->firstname    = $speaker->getFirstname();
        $this->lastname     = $speaker->getLastname();
        $this->organization = $speaker->getOrganization();

        /**
         * @var SpeakerTranslation
         */
        foreach ($speaker->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'position' => $translation->getPosition(),
            ];
        }
    }
}
