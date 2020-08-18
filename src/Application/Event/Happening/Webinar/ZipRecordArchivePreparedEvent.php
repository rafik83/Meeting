<?php

namespace Proximum\Vimeet\Application\Event\Happening\Webinar;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

class ZipRecordArchivePreparedEvent extends SymfonyEvent
{
    /** @var Happening */
    private $happening;

    /** @var Admin */
    private $admin;

    /** @var string */
    private $locale;

    public function __construct(
        Happening $happening,
        Admin $admin,
        string $locale
    ) {
        $this->happening = $happening;
        $this->admin = $admin;
        $this->locale = $locale;
    }

    public function getHappening(): Happening
    {
        return $this->happening;
    }

    public function getEvent(): Event
    {
        return $this->happening->getEvent();
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
