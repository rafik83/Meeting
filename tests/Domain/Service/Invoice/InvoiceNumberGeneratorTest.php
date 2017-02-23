<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Service\Invoice;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Service\Invoice\InvoiceNumberGenerator;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class InvoiceNumberGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var \DateTime
     */
    private $datetime;

    /**
     * @var InvoiceNumberGenerator
     */
    private $invoiceNumberGenerator;


    public function setUp()
    {
        $this->datetime = new \DateTime();
        $this->event = EventFactory::createEvent();
        $this->invoiceNumberGenerator = new InvoiceNumberGenerator();
    }

    public function testIncrementInvoiceNumberWithoutExistingInvoice()
    {
        $invoiceNumber = $this->invoiceNumberGenerator->generate();
        $expectedInvoiceIncrement = '0001';
        $this->assertEquals($expectedInvoiceIncrement, $invoiceNumber, "Invoice increment should be :" . $expectedInvoiceIncrement);
    }

    public function testIncrementInvoiceNumberWithExistingInvoices()
    {
        $type = new Type($this->event);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($this->event, $type, [], $owner, $this->datetime);
        $invoice = new Invoice($this->event, $sheet, $this->event->getInvoicePrefix(), 'Vi', date('Y'), '0888', 10, 10, 10, $this->datetime);
        $expectedInvoiceIncrement = "0889";
        $invoiceIncrement = $this->invoiceNumberGenerator->generate($invoice);
        $this->assertEquals($expectedInvoiceIncrement, $invoiceIncrement, "Invoice increment should be :" . $expectedInvoiceIncrement);
    }

    public function testIncrementDate()
    {
        $type = new Type($this->event);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($this->event, $type, [], $owner, $this->datetime);
        $invoice = new Invoice($this->event, $sheet, $this->event->getInvoicePrefix(), 'Vi', '2016', '0001', 10, 10, 10, new \DateTime('2016-12-31'));
        $expectedInvoiceIncrement = "0001";
        $invoiceNumber = $this->invoiceNumberGenerator->generate($invoice);
        $this->assertEquals($expectedInvoiceIncrement, $invoiceNumber, "Invoice number should be :" . $expectedInvoiceIncrement);
    }
}
