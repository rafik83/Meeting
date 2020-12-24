<?php

namespace Proximum\Vimeet\Application\Command\Order\Export;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class ExportJobCreator
{
    /** @var Event */
    public $event;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     */
    public function __construct(Event $event, Admin $admin, $locale)
    {
        $this->event  = $event;
        $this->admin  = $admin;
        $this->locale = $locale;
    }
}
