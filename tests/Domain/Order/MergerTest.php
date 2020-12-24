<?php

namespace Proximum\Vimeet\Domain\Order;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Exception\Order\OrderMergerException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class MergerTest extends TestCase
{
    public function testMerge()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $package  = new Package($event, 'package', $datetime);
        $type->setPackage($package);
        $owner = new User('test@test.fr', '__SALT__', '__PASSWORD__', 'fr');
        $sheet = new Sheet($event, $type, [], $owner, $datetime);

        $plan        = Product::createPlan($event, 'plan', '', 200, 20, 20, 100);
        $participant = Product::createParticipant($event, 'participant', 1250, 20, 20);
        $option      = Product::createOption($event, 'option', '', 99, 20, 50, 10, 20, true);

        // Setup
        $orderOne = new Order($sheet, '[]', $datetime->modify('-5 day'));
        $row = new Order\Row($orderOne, 1, 20, $plan);
        $orderOne->addRow($row);
        $orderOne->addRow(new Order\Row($orderOne, 2, 20, $participant));
        $orderOne->addRow(new Order\Row($orderOne, 1, 20, $option));

        $rowToRemove = $this->prophesize(Row::class);
        $rowToRemove->getVatRate()->shouldBeCalled()->willReturn(20);
        $rowToRemove->getQuantity()->shouldBeCalled()->willReturn(0);
        $rowToRemove->hasParentRow()->shouldBeCalled()->willReturn(true);
        $rowToRemove->getParentRow()->shouldBeCalled()->willReturn($row);
        $rowToRemove->getLabel()->shouldBeCalled()->willReturn('label row to remove');
        $rowToRemove->getPrice()->shouldBeCalled()->willReturn(10);

        $rowWithoutIdWithQuantity = $this->prophesize(Row::class);
        $rowWithoutIdWithQuantity->getQuantity()->shouldBeCalled()->willReturn(1);
        $rowWithoutIdWithQuantity->hasParentRow()->shouldBeCalled()->willReturn(false);
        $rowWithoutIdWithQuantity->getProduct()->shouldBeCalled()->willReturn($participant);

        $orderOne->addRow($rowToRemove->reveal());
        $orderOne->addRow($rowWithoutIdWithQuantity->reveal());
        $sheet->addOrder($orderOne);

        $orderTwo = new Order($sheet, '[]', $datetime->modify('-2 day'));
        $orderTwo->addRow(new Order\Row($orderTwo, -1, 20, $participant));
        $orderTwo->addRow(new Order\Row($orderTwo, 3, 20, $option));
        $sheet->addOrder($orderTwo);

        $orderMerger = new Merger();
        $order       = $orderMerger->merge([$orderOne, $orderTwo]);

        $this->assertCount(4, $order->getRows());
        $this->assertEquals(1, $order->getRowForProduct($plan)->getQuantity());
        $this->assertEquals(2, $order->getRowForProduct($participant)->getQuantity());
        $this->assertEquals(4, $order->getRowForProduct($option)->getQuantity());
    }

    public function testOrderMergerException()
    {
        $this->expectException(OrderMergerException::class);

        $orderMerger = new Merger();
        $orderMerger->merge([]);
    }

    public function test_child_row_with_parent_row_removed()
    {
        $datetime = new \DateTime();

        $event = $this->prophesize(Event::class);
        $event->getCurrency()->willReturn('EUR');
        $event->getVat()->willReturn(20);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->willReturn($event->reveal());

        $option1 = Product::createOption($event->reveal(), 'option', '', 99, 20, 50, 10, 20, true);
        $option2 = Product::createOption($event->reveal(), 'option', '', 150, 20, 50, 10, 20, true);

        $order1 = new Order($sheet->reveal(), '[]', $datetime);
        $order1row1 = new Order\Row($order1, 2, 20, $option1, 1337);
        $order1row1child1 = new Order\Row($order1, 2, 20, null, 1337, 'Custom row 1', 399, $order1row1);
        $order1row2 = new Order\Row($order1, 2, 20, $option2);
        $order1->addRow($order1row1);
        $order1->addRow($order1row1child1);
        $order1->addRow($order1row2);

        $order2 = new Order($sheet->reveal(), '[]', $datetime);
        $order2row1 = new Order\Row($order2, -2, 20, $option1, 1337);
        $order2row2 = new Order\Row($order2, 1, 20, $option2);
        $order2->addRow($order2row1);
        $order2->addRow($order2row2);

        $orderMerger = new Merger();
        $orderMerged = $orderMerger->merge([$order1, $order2]);

        $this->assertEquals(
            [
                new Order\Row($orderMerged, 3, 20, $option2),
                new Order\Row($orderMerged, 2, 20, null, 1337, 'Custom row 1', 399, null),
            ],
            array_values($orderMerged->getRows())
        );
    }

    public function test_groups_merge(): void
    {
        $datetime = new \DateTime();

        $event = $this->prophesize(Event::class);
        $event->getCurrency()->willReturn('EUR');
        $event->getVat()->willReturn(20);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getEvent()->willReturn($event->reveal());

        $order1 = new Order($sheet->reveal(), '{"1": "foo", "2": "bar"}', $datetime);
        $order2 = new Order($sheet->reveal(), '{"2": "bar", "3": "foobar"}', $datetime);

        $orderMerger = new Merger();
        $orderMerger = $orderMerger->merge([$order1, $order2]);

        $expected = '{"1":"foo","2":"bar","3":"foobar"}';
        $this->assertEquals($expected, $orderMerger->getGroupsData());
    }
}
