<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\Invoice;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;

class InvoiceNumberGenerator
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * InvoiceNumberGenerator constructor.
     *
     * @param Event $event
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(Event $event, \DateTimeInterface $dateTime)
    {
        $this->event    = $event;
        $this->datetime = $dateTime;
    }

    /**
     * Generate Invoice Number based on format : [Prefix][Year]-[Number]
     * Example : "Vi2017-0001"
     *
     * @param Invoice $invoice|null
     *
     * @return string
     */
    public function generate(Invoice $invoice = null)
    {
        return sprintf(
          '%s%s-%s',
            $this->event->getInvoicePrefix()->getPrefix(),
            $this->datetime->format('Y'),
            str_pad($this->getIncrementalInvoiceNumber($invoice), 4, "0", STR_PAD_LEFT)
        );
    }

    /**
     * @param Invoice $invoice|null
     *
     * @return int
     */
    private function getIncrementalInvoiceNumber(Invoice $invoice = null)
    {
        if ($invoice === null) {
            return 1;
        }

        if ($invoice->getInvoiceYear() === date('Y')) {
            return $this->incrementInvoiceNumber($invoice->getInvoiceNumber());
        }

        return 1;
    }

    /**
     * Increment a given number
     *
     * @param int $number
     *
     * @return int
     */
    private function incrementInvoiceNumber($number)
    {
        return (int) $number + 1;
    }
}
