<?php

namespace Proximum\Vimeet\Tests\Application\Query\Package\Planning;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Package\Planning\PlanningViewQuery;
use Proximum\Vimeet\Application\Query\Package\Planning\PlanningViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\ProductView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\PackageGroup;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PlanningViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $now     = new \DateTime();
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet   = new Sheet($event, $type, [], $user, $now);
        $locale  = 'fr';

        $package = new Package($event, 'package', $now);
        $participantProduct = new Product(
            $event,
            'participant',
            'nameParticipant',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $planningProduct = new Product(
            $event,
            'planning',
            'namePlanning',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );
        $planningProduct->translate('fr', 'title', 'heading', 'description', 'addon', 'subjectedToValidationHelp');

        $planProduct = new Product(
            $event,
            'plan',
            'namePlan',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $optionProduct = new Product(
            $event,
            'option',
            'nameOption',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $package->setPlans([$planProduct]);
        $package->setParticipants([$participantProduct]);
        $package->setPlanning($planningProduct);
        $package->setGroupsOptions([[$optionProduct]]);

        $type->setPackage($package);

        $order = new Order(
            $sheet,
            '',
            $now
        );
        $rowPlan        = new Order\Row($order, 1, 20, $planProduct);
        $rowParticipant = new Order\Row($order, 1, 20, $participantProduct);
        $rowPlanning    = new Order\Row($order, 1, 20, $planningProduct);
        $order->addRow($rowPlan);
        $order->addRow($rowParticipant);
        $order->addRow($rowPlanning);

        $planningViewQuery = new PlanningViewQuery($sheet, $planningProduct, $locale);

        $cart = new Cart($sheet, [], [], null);

        $sheet->addOrder($order);

        // Mock
        $orderMeger  = $this->prophesize(Merger::class);
        $orderMeger->merge([$order])->shouldBeCalled()->willReturn($order);
        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);

        $expectedPlanningView = new ProductView(
            null,
            'title', // $title,
            10, // $unitPrice,
            'heading', // $heading,
            'description', // $description,
            'addon', // $addon,
            'image.jpeg', // $image,
            5, // $availabilityCurrent,
            5, // $availabilityMax,
            false, // $isOutOfStock,
            $event->getMode(), // $vatMode,
            $event->getCurrency(), // $currency,
            'subjectedToValidationHelp', // $subjectedToValidationHelp,
            false, // $isSubjectedToValidation,
            0, // $included,
            true // $isBuyable
        );

        $planningViewQueryHandler = new PlanningViewQueryHandler(
            $cartManager->reveal(),
            $orderMeger->reveal(),
            $now
        );

        $this->assertEquals($expectedPlanningView, $planningViewQueryHandler->handle($planningViewQuery));
    }

    public function testHandleWithIncludedFromOrder()
    {
        $now     = new \DateTime();
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet   = new Sheet($event, $type, [], $user, $now);
        $locale  = 'fr';

        $package = new Package($event, 'package', $now);
        $participantProduct = new Product(
            $event,
            'participant',
            'nameParticipant',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $planningProduct = new Product(
            $event,
            'planning',
            'namePlanning',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );
        $planningProduct->translate('fr', 'title', 'heading', 'description', 'addon', 'subjectedToValidationHelp');

        $planProduct = new Product(
            $event,
            'plan',
            'namePlan',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );
        $planProduct->includeProduct($planningProduct, 1);
        $planProduct->includeProduct($participantProduct, 1);

        $optionProduct = new Product(
            $event,
            'option',
            'nameOption',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $package->setPlans([$planProduct]);
        $package->setParticipants([$participantProduct]);
        $package->setPlanning($planningProduct);
        $package->setGroupsOptions([[$optionProduct]]);

        $type->setPackage($package);

        $order = new Order(
            $sheet,
            '',
            $now
        );
        $rowPlan        = new Order\Row($order, 1, 20, $planProduct);
        $rowParticipant = new Order\Row($order, 1, 20, $participantProduct);
        $rowPlanning    = new Order\Row($order, 1, 20, $planningProduct);
        $order->addRow($rowPlan);
        $order->addRow($rowParticipant);
        $order->addRow($rowPlanning);

        $planningViewQuery = new PlanningViewQuery($sheet, $planningProduct, $locale);

        $cart = new Cart($sheet, [], [], null);

        $sheet->addOrder($order);

        // Mock
        $orderMeger  = $this->prophesize(Merger::class);
        $orderMeger->merge([$order])->shouldBeCalled()->willReturn($order);
        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);

        $expectedPlanningView = new ProductView(
            null,
            'title', // $title,
            10, // $unitPrice,
            'heading', // $heading,
            'description', // $description,
            'addon', // $addon,
            'image.jpeg', // $image,
            5, // $availabilityCurrent,
            5, // $availabilityMax,
            false, // $isOutOfStock,
            $event->getMode(), // $vatMode,
            $event->getCurrency(), // $currency,
            'subjectedToValidationHelp', // $subjectedToValidationHelp,
            false, // $isSubjectedToValidation,
            1, // $included,
            true // $isBuyable
        );

        $planningViewQueryHandler = new PlanningViewQueryHandler(
            $cartManager->reveal(),
            $orderMeger->reveal(),
            $now
        );

        $this->assertEquals($expectedPlanningView, $planningViewQueryHandler->handle($planningViewQuery));
    }

    public function testHandleWithMultipleIncludedFromOrder()
    {
        $now     = new \DateTime();
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet   = new Sheet($event, $type, [], $user, $now);
        $locale  = 'fr';

        $package = new Package($event, 'package', $now);
        $participantProduct = new Product(
            $event,
            'participant',
            'nameParticipant',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $planningProduct = new Product(
            $event,
            'planning',
            'namePlanning',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );
        $planningProduct->translate('fr', 'title', 'heading', 'description', 'addon', 'subjectedToValidationHelp');

        $planProduct = new Product(
            $event,
            'plan',
            'namePlan',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );
        $planProduct->includeProduct($planningProduct, 3);
        $planProduct->includeProduct($participantProduct, 4);

        $optionProduct = new Product(
            $event,
            'option',
            'nameOption',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $package->setPlans([$planProduct]);
        $package->setParticipants([$participantProduct]);
        $package->setPlanning($planningProduct);
        $package->setGroupsOptions([[$optionProduct]]);

        $type->setPackage($package);

        $order = new Order(
            $sheet,
            '',
            $now
        );
        $rowPlan        = new Order\Row($order, 1, 20, $planProduct);
        $rowParticipant = new Order\Row($order, 1, 20, $participantProduct);
        $rowPlanning    = new Order\Row($order, 1, 20, $planningProduct);
        $order->addRow($rowPlan);
        $order->addRow($rowParticipant);
        $order->addRow($rowPlanning);

        $planningViewQuery = new PlanningViewQuery($sheet, $planningProduct, $locale);

        $cart = new Cart($sheet, [], [], null);

        $sheet->addOrder($order);

        // Mock
        $orderMeger  = $this->prophesize(Merger::class);
        $orderMeger->merge([$order])->shouldBeCalled()->willReturn($order);
        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);

        $expectedPlanningView = new ProductView(
            null,
            'title', // $title,
            10, // $unitPrice,
            'heading', // $heading,
            'description', // $description,
            'addon', // $addon,
            'image.jpeg', // $image,
            5, // $availabilityCurrent,
            5, // $availabilityMax,
            false, // $isOutOfStock,
            $event->getMode(), // $vatMode,
            $event->getCurrency(), // $currency,
            'subjectedToValidationHelp', // $subjectedToValidationHelp,
            false, // $isSubjectedToValidation,
            3, // $included,
            true // $isBuyable
        );

        $planningViewQueryHandler = new PlanningViewQueryHandler(
            $cartManager->reveal(),
            $orderMeger->reveal(),
            $now
        );

        $this->assertEquals($expectedPlanningView, $planningViewQueryHandler->handle($planningViewQuery));
    }

    public function testHandleWithIncludedFromCart()
    {
        $now     = new \DateTime();
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet   = new Sheet($event, $type, [], $user, $now);
        $locale  = 'fr';

        $package = new Package($event, 'package', $now);
        $participantProduct = new Product(
            $event,
            'participant',
            'nameParticipant',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $planningProduct = new Product(
            $event,
            'planning',
            'namePlanning',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );
        $planningProduct->translate('fr', 'title', 'heading', 'description', 'addon', 'subjectedToValidationHelp');

        $planProduct = new Product(
            $event,
            'plan',
            'namePlan',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );
        $planProduct->includeProduct($planningProduct, 1);
        $planProduct->includeProduct($participantProduct, 1);

        $optionProduct = new Product(
            $event,
            'option',
            'nameOption',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $package->setPlans([$planProduct]);
        $package->setParticipants([$participantProduct]);
        $package->setPlanning($planningProduct);
        $package->setGroupsOptions([[$optionProduct]]);

        $type->setPackage($package);

        $planningViewQuery = new PlanningViewQuery($sheet, $planningProduct, $locale);

        $cart = new Cart($sheet, [], [], null);
        $cart->setProduct($planProduct, 1);

        // Mock
        $orderMeger  = $this->prophesize(Merger::class);
        $orderMeger->merge([])->shouldNotBeCalled();
        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);

        $expectedPlanningView = new ProductView(
            null,
            'title', // $title,
            10, // $unitPrice,
            'heading', // $heading,
            'description', // $description,
            'addon', // $addon,
            'image.jpeg', // $image,
            5, // $availabilityCurrent,
            5, // $availabilityMax,
            false, // $isOutOfStock,
            $event->getMode(), // $vatMode,
            $event->getCurrency(), // $currency,
            'subjectedToValidationHelp', // $subjectedToValidationHelp,
            false, // $isSubjectedToValidation,
            1, // $included,
            true // $isBuyable
        );

        $planningViewQueryHandler = new PlanningViewQueryHandler(
            $cartManager->reveal(),
            $orderMeger->reveal(),
            $now
        );

        $this->assertEquals($expectedPlanningView, $planningViewQueryHandler->handle($planningViewQuery));
    }

    public function testHandleWithMultipleIncludedFromCart()
    {
        $now     = new \DateTime();
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $sheet   = new Sheet($event, $type, [], $user, $now);
        $locale  = 'fr';

        $package = new Package($event, 'package', $now);
        $participantProduct = new Product(
            $event,
            'participant',
            'nameParticipant',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $planningProduct = new Product(
            $event,
            'planning',
            'namePlanning',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );
        $planningProduct->translate('fr', 'title', 'heading', 'description', 'addon', 'subjectedToValidationHelp');

        $planProduct = new Product(
            $event,
            'plan',
            'namePlan',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );
        $planProduct->includeProduct($planningProduct, 3);
        $planProduct->includeProduct($participantProduct, 4);

        $optionProduct = new Product(
            $event,
            'option',
            'nameOption',
            'image.jpeg',
            10, // UnitPrice
            20, //vat
            10, // $quantityMax
            5, // $availabilityCurrent
            5, // $availabilityMax
            true, // $updatable
            null, // $deletableUntil
            false, // $subjectedToValidation
            null // $buyableUntil
        );

        $package->setPlans([$planProduct]);
        $package->setParticipants([$participantProduct]);
        $package->setPlanning($planningProduct);
        $package->setGroupsOptions([[$optionProduct]]);

        $type->setPackage($package);

        $planningViewQuery = new PlanningViewQuery($sheet, $planningProduct, $locale);

        $cart = new Cart($sheet, [], [], null);
        $cart->setProduct($planProduct, 1);

        // Mock
        $orderMeger  = $this->prophesize(Merger::class);
        $orderMeger->merge([])->shouldNotBeCalled();
        $cartManager = $this->prophesize(CartManager::class);
        $cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart);

        $expectedPlanningView = new ProductView(
            null,
            'title', // $title,
            10, // $unitPrice,
            'heading', // $heading,
            'description', // $description,
            'addon', // $addon,
            'image.jpeg', // $image,
            5, // $availabilityCurrent,
            5, // $availabilityMax,
            false, // $isOutOfStock,
            $event->getMode(), // $vatMode,
            $event->getCurrency(), // $currency,
            'subjectedToValidationHelp', // $subjectedToValidationHelp,
            false, // $isSubjectedToValidation,
            3, // $included,
            true // $isBuyable
        );

        $planningViewQueryHandler = new PlanningViewQueryHandler(
            $cartManager->reveal(),
            $orderMeger->reveal(),
            $now
        );

        $this->assertEquals($expectedPlanningView, $planningViewQueryHandler->handle($planningViewQuery));
    }
}
