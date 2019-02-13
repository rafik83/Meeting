<?php

namespace Proximum\Vimeet\Application\Command\Product\Export;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class ExportProductsJobCreator
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
    public function __construct(Event $event, Admin $admin, string $locale)
    {
        $this->event  = $event;
        $this->admin  = $admin;
        $this->locale = $locale;
    }
}
