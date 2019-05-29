<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class BatchPrintInvoicesJobCreator extends AbstractBatch
{
    /** @var string */
    public $locale;

    /** @var string */
    public $emailToNotify;

    /** @var array */
    public $sheetIds;

    /** @var Event */
    public $event;

    /**
     * @param Event  $event
     * @param int[]  $sheetIds
     * @param Admin  $admin
     * @param string $locale
     */
    public function __construct(Event $event, array $sheetIds, Admin $admin, string $locale)
    {
        $this->event = $event;
        $this->sheetIds = $sheetIds;
        $this->emailToNotify = $admin->getEmail();
        $this->locale = $locale;
    }
}
