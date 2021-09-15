<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Planning;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Product\Planning\UpdatePlanning;
use Proximum\Vimeet\Application\Command\Product\Planning\UpdatePlanningHandler;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdatePlanningHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $name        = 'Name';
        $unitPrice   = 100;
        $quantityMax = 4;
        $vat = 20;

        $planning = Product::createPlanning(
            $event,
            $name,
            $unitPrice,
            $vat,
            $quantityMax
        );

        $expectedPlanning = Product::createPlanning(
            $event,
            'my planning updated',
            $unitPrice,
            19,
            $quantityMax
        );

        // set translations to empty
        foreach ($event->getLocales() as $locale) {
            $expectedPlanning->translate($locale, '', '', '', '', '');
        }

        // Command
        $updatePlanningCommand       = new UpdatePlanning($planning);
        $updatePlanningCommand->name = 'my planning updated';
        $updatePlanningCommand->unitPrice = $unitPrice;
        $updatePlanningCommand->vat = 19;

        // Mock
        $productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $productRepository->update(Argument::that(function (Product $givenPlanning) use ($expectedPlanning) {
            return $givenPlanning->getType() === $expectedPlanning->getType()
                && $givenPlanning->getVat() === $expectedPlanning->getVat()
                && $givenPlanning->getUnitPrice() === $expectedPlanning->getUnitPrice()
                && $givenPlanning->getAvailabilityCurrent() === $expectedPlanning->getAvailabilityCurrent()
                && $givenPlanning->getAvailabilityMax() === $expectedPlanning->getAvailabilityMax()
                && $givenPlanning->getName() === $expectedPlanning->getName()
                ;
        }))->shouldBeCalled();

        $updatePriceResolver = $this->prophesize(UpdatePriceResolver::class);
        $updatePriceResolver->resolve($planning)->shouldBeCalled()->willReturn(true);

        // Handler
        $handler = new UpdatePlanningHandler(
            $productRepository->reveal(),
            $updatePriceResolver->reveal()
        );
        $handler->handle($updatePlanningCommand);
    }
}
