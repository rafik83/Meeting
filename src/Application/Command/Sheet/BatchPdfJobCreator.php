<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class BatchPdfJobCreator extends AbstractBatch
{
    /** @var string */
    public $locale;

    /** @var string */
    public $emailToNotify;

    /** @var array */
    public $sheetIds;

    /** @var Event */
    public $event;

    /** @var string */
    public $orderBy;

    /**
     * @param Event  $event
     * @param array  $sheetIds
     * @param Admin  $admin
     * @param string $locale
     * @param string $orderBy
     */
    public function __construct(Event $event, array $sheetIds, Admin $admin, string $locale, string $orderBy)
    {
        $this->event         = $event;
        $this->emailToNotify = $admin->getEmail();
        $this->locale        = $locale;
        $this->sheetIds      = $sheetIds;
        $this->orderBy       = $orderBy;
    }
}
