<?php

namespace Proximum\Vimeet\Tests\Domain\Order\Numero;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Numero\Generator;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class GeneratorTest extends TestCase
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var Order
     */
    private $order;

    /**
     * Init mock for the suite test
     */
    public function setUp()
    {
        $date        = new \DateTime();
        $this->event = EventFactory::createEvent();
        $this->sheet = SheetFactory::create($this->event);
        $this->order = new Order($this->sheet, '', $date);
    }

    public function testGenerate()
    {
        $this->changeEventId(2);
        $this->changesheetId(2);
        $this->changeOrderId(2);
        $this->assertEquals(
            '02-02-02',
            Generator::generate(
                $this->order
            )
        );

        $this->changeEventId(5);
        $this->changesheetId(3);
        $this->changeOrderId(1);
        $this->assertEquals(
            '05-03-01',
            Generator::generate(
                $this->order
            )
        );

        $this->changeEventId(12);
        $this->changesheetId(3);
        $this->changeOrderId(88);
        $this->assertEquals(
            '12-03-88',
            Generator::generate(
                $this->order
            )
        );

        $this->changeEventId(345);
        $this->changesheetId(321);
        $this->changeOrderId(112);
        $this->assertEquals(
            '345-321-112',
            Generator::generate(
                $this->order
            )
        );

        $this->changeEventId(54321);
        $this->changesheetId(32100);
        $this->changeOrderId(12345);
        $this->assertEquals(
            '54321-32100-12345',
            Generator::generate(
                $this->order
            )
        );
    }

    /**
     * @param int $id
     */
    public function changeEventId($id)
    {
        $reflection  = new \ReflectionClass(Event::class);
        $property    = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->event, $id);
        $property->setAccessible(false);
    }

    /**
     * @param int $id
     */
    public function changeSheetId($id)
    {
        $reflection  = new \ReflectionClass(Sheet::class);
        $property    = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->sheet, $id);
        $property->setAccessible(false);
    }

    /**
     * @param int $id
     */
    public function changeOrderId($id)
    {
        $reflection  = new \ReflectionClass(Order::class);
        $property    = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->order, $id);
        $property->setAccessible(false);
    }
}
