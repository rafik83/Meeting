<?php

namespace Proximum\Vimeet\Application\Event\Sheet;

use Proximum\Vimeet\Application\View\Sheet\SheetInvoicedView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher;

class SheetInvoicedEvent extends EventDispatcher\Event
{
    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @var SheetInvoicedView[]
     */
    private $sheetInvoicedViews;

    /**
     * SheetInvoicedEvent constructor.
     *
     * @param Admin               $admin
     * @param Event               $event
     * @param \DateTimeInterface  $date
     * @param SheetInvoicedView[] $sheetInvoicedViews
     */
    public function __construct(Admin $admin, Event $event, \DateTimeInterface $date, array $sheetInvoicedViews)
    {
        $this->admin              = $admin;
        $this->event              = $event;
        $this->date               = $date;
        $this->sheetInvoicedViews = $sheetInvoicedViews;
    }

    /**
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return SheetInvoicedView[]
     */
    public function getSheetInvoicedViews()
    {
        return $this->sheetInvoicedViews;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return array_map(function (SheetInvoicedView $invoicedView) {
            return $invoicedView->sheet;
        }, $this->sheetInvoicedViews);
    }
}
