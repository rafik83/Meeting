<?php

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Create implements Command
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var string
     */
    public $organization;

    /**
     * @var UploadedFile
     */
    public $logo;

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
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'position' => '',
            ];
        }
    }
}
