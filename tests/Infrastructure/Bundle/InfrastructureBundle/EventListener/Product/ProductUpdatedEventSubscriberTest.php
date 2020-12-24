<?php


namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Event\Product\ProductUpdatedEvent;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\GenerateParticipantProductSystemUnavailabilities;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Product\ProductUpdatedEventSubscriber;

class ProductUpdatedEventSubscriberTest extends TestCase
{
    public function testIsNotParticipantProduct()
    {
        $generateParticipantProductSystemUnavailabilities = $this->prophesize(GenerateParticipantProductSystemUnavailabilities::class);

        $productUpdatedEventSubscriber = new ProductUpdatedEventSubscriber($generateParticipantProductSystemUnavailabilities->reveal());

        $product = $this->prophesize(Product::class);
        $product->isParticipant()->shouldBeCalled()->willReturn(false);

        $generateParticipantProductSystemUnavailabilities->__invoke($product->reveal())->shouldNotBeCalled();

        $event = new ProductUpdatedEvent($product->reveal(), []);
        $productUpdatedEventSubscriber->onProductUpdated($event);
    }

    public function testAvailabilityTimeRangesDidntChanged()
    {
        $generateParticipantProductSystemUnavailabilities = $this->prophesize(GenerateParticipantProductSystemUnavailabilities::class);

        $productUpdatedEventSubscriber = new ProductUpdatedEventSubscriber($generateParticipantProductSystemUnavailabilities->reveal());

        $timeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $timeRange1->getId()->willReturn(1);
        $timeRange2 = $this->prophesize(AvailabilityTimeRange::class);
        $timeRange2->getId()->willReturn(1);

        $product = $this->prophesize(Product::class);
        $product->isParticipant()->shouldBeCalled()->willReturn(true);
        $product->getAvailabilityTimeRanges()->shouldBeCalled()->willReturn(
            [
                $timeRange2->reveal(),
                $timeRange1->reveal(),
            ]
        );

        $generateParticipantProductSystemUnavailabilities->__invoke($product->reveal())->shouldNotBeCalled();

        $event = new ProductUpdatedEvent($product->reveal(), [$timeRange1->reveal(), $timeRange2->reveal()]);
        $productUpdatedEventSubscriber->onProductUpdated($event);
    }

    public function testAvailabilityTimeRangesChanged()
    {
        $generateParticipantProductSystemUnavailabilities = $this->prophesize(GenerateParticipantProductSystemUnavailabilities::class);

        $productUpdatedEventSubscriber = new ProductUpdatedEventSubscriber($generateParticipantProductSystemUnavailabilities->reveal());

        $timeRange1 = $this->prophesize(AvailabilityTimeRange::class);
        $timeRange1->getId()->willReturn(1);
        $timeRange2 = $this->prophesize(AvailabilityTimeRange::class);
        $timeRange2->getId()->willReturn(3);
        $timeRange3 = $this->prophesize(AvailabilityTimeRange::class);
        $timeRange3->getId()->willReturn(2);

        $product = $this->prophesize(Product::class);
        $product->isParticipant()->shouldBeCalled()->willReturn(true);
        $product->getAvailabilityTimeRanges()->shouldBeCalled()->willReturn(
            [
                $timeRange2->reveal(),
                $timeRange1->reveal()
            ]
        );

        $generateParticipantProductSystemUnavailabilities->__invoke($product->reveal())->shouldBeCalled();

        $event = new ProductUpdatedEvent($product->reveal(), [$timeRange1->reveal(), $timeRange2->reveal(), $timeRange3->reveal()]);
        $productUpdatedEventSubscriber->onProductUpdated($event);
    }
}
