<?php

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Model\Happening\SpeakerTranslation;
use Proximum\Vimeet\Domain\Model\User;
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
     * @var null|string
     */
    public $email;

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
        if ($speaker->getUser()) {
            $this->email = $speaker->getUser()->getEmail();
        }

        /*
         * @var SpeakerTranslation
         */
        foreach ($speaker->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'position' => $translation->getPosition(),
            ];
        }
    }
}
