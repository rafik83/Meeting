<?php

namespace Proximum\Vimeet\Tests\Domain\Service\Invoice;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Invoice\InvoiceNumberGenerator;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class InvoiceNumberGeneratorTest extends TestCase
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var \DateTime
     */
    private $dateTime;

    /**
     * @var InvoiceNumberGenerator
     */
    private $invoiceNumberGenerator;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    public function setUp()
    {
        $this->dateTime               = new \DateTime();
        $this->event                  = EventFactory::createEvent();
        $this->invoiceNumberGenerator = new InvoiceNumberGenerator();
        $this->invoiceRepository      = $this->prophesize(InvoiceRepositoryInterface::class)->reveal();
    }

    public function testIncrementInvoiceNumberWithoutExistingInvoice()
    {
        $invoiceNumber = $this->invoiceNumberGenerator->generate();
        $expectedInvoiceIncrement = '0001';
        $this->assertEquals(
            $expectedInvoiceIncrement,
            $invoiceNumber,
            'Invoice increment should be :' . $expectedInvoiceIncrement
        );
    }

    public function testIncrementInvoiceNumberWithExistingInvoices()
    {
        $type    = new Type($this->event);
        $owner   = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet   = new Sheet($this->event, $type, [], $owner, $this->dateTime);
        $invoice = new Invoice(
            $this->event,
            $sheet,
            $this->event->getInvoicePrefix(),
            'Vi',
            date('Y'),
            888,
            true,
            'et',
            20,
            10,
            10,
            10,
            'EUR',
            'some-data',
            $this->dateTime
        );
        $expectedInvoiceIncrement = '0889';
        $invoiceIncrement = $this->invoiceNumberGenerator->generate($invoice);
        $this->assertEquals(
            $expectedInvoiceIncrement,
            $invoiceIncrement,
            'Invoice increment should be :' . $expectedInvoiceIncrement
        );
    }

    public function testIncrementDate()
    {
        $prefix  = new Prefix('Asddays', 'As');
        $invoice = $this->invoiceRepository->getLastInvoiceForEventPrefix($prefix, 2017);

        $expectedInvoiceIncrement = '0001';
        $invoiceNumber            = $this->invoiceNumberGenerator->generate($invoice);
        $this->assertEquals(
            $expectedInvoiceIncrement,
            $invoiceNumber,
            'Invoice number should be :' . $expectedInvoiceIncrement
        );
    }
}
