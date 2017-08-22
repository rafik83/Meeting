<?php


namespace Proximum\Vimeet\Tests\Application\Command\Product\Plan;


use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Product\Plan\UpdatePlan;
use Proximum\Vimeet\Application\Command\Product\Plan\UpdatePlanHandler;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class UpdatePlanHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name = 'Name';
        $image = 'Image';
        $unitPrice = 100;
        $availabilityCurrent = 10;
        $availabilityMax = 50;

        $product = Product::createOption($event, 'Option A', 'a.jpg', 100, 2, 4, 3, false);
        $product->translate('fr', 'foo', null, 'bar', 'optional', null);
        $product->translate('en', 'enfoo', null, 'enbar', 'enoptional', null);

        $plan = Product::createPlan(
            $event,
            $name,
            $image,
            $unitPrice,
            $availabilityCurrent,
            $availabilityMax
        );

        $expectedPlan = Product::createPlan(
            $event,
            'name plan modify',
            $image,
            $unitPrice,
            $availabilityCurrent,
            $availabilityMax
        );

        $updateCommand = new UpdatePlan($plan);
        $updateCommand->name = 'name plan modify';

        // Mock
        $pacakgeRepository = $this->prophesize(ProductRepositoryInterface::class);
        $pacakgeRepository->update($plan)->shouldBeCalled();

        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage->upload(null)->shouldBeCalled()->willReturn('Image');

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);
        $updatePriceResolver->resolve($plan)->shouldBeCalled();

        // Handler
        $handler = new UpdatePlanHandler(
            $pacakgeRepository->reveal(),
            $updatePriceResolver->reveal(),
            $fileStorage->reveal()
        );
        $handler->handle($updateCommand);
    }
    
}
