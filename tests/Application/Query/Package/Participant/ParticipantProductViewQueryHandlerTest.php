<?php

namespace Proximum\Vimeet\Tests\Application\Query\Package\Participant;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Package\ProductByParticipantCartGetter;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductTranslation;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Package\Product\IncludedParticipantGuesser;
use Proximum\Vimeet\Domain\View\Package\Product\IncludedParticipantView;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;

class ParticipantProductViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $includedParticipantGuesser;

    /** @var ObjectProphecy */
    private $productByParticipantGetter;

    /** @var ObjectProphecy */
    private $cartManager;

    public function setUp()
    {
        $this->includedParticipantGuesser = $this->prophesize(IncludedParticipantGuesser::class);
        $this->productByParticipantGetter = $this->prophesize(ProductByParticipantCartGetter::class);
        $this->cartManager = $this->prophesize(CartManager::class);
    }

    /**
     * Test included participant remaining > 0
     */
    public function testRemainingQuantityHandle()
    {
        $locale = 'fr';
        $date = new \DateTime();

        $event = EventFactory::createEvent();

        $plan = Product::createPlan($event, 'My plan', '', 99, 20, 0, 0);
        $participantProduct = Product::createParticipant($event, 'My participant product', 49, 20, 2);
        $this->setPropertyValue($participantProduct, 'id', 1337);
        $participantIncludedProductTranslationFr = new ProductTranslation(
            $participantProduct,
            $locale,
            'Participant inclus',
            '',
            'Description Participant inclus',
            '',
            ''
        );

        $translations = new ArrayCollection();
        $translations[$locale] = $participantIncludedProductTranslationFr;
        $participantProduct->setTranslations($translations);

        $plan->includeProduct($participantProduct, 2);

        $package = new Package($event, 'Package', $date);
        $package->setPlans([$plan]);
        $package->setParticipants([$participantProduct]);
        $package->enable(true, false, false);

        $type = new Type($event);
        $type->setPackage($package);

        $sheet = new Sheet($event, $type, [], new User('user@vimeet.com', 'salt', 'password', 'fr'), $date);
        $participant = ParticipantFactory::create($sheet);
        $sheet->addParticipant($participant);
        $this->setPropertyValue($participant, 'id', 7331);

        $cart = $this->prophesize(Cart::class);
        $productParticipants = [
            7331 => null,
        ];
        $this->cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart->reveal());
        $this->productByParticipantGetter
            ->getFromCart($cart->reveal())
            ->shouldBeCalled()
            ->willReturn($productParticipants)
        ;

        $handler = new ParticipantProductViewQueryHandler(
            $this->includedParticipantGuesser->reveal(),
            $this->productByParticipantGetter->reveal(),
            $this->cartManager->reveal()
        );

        $this->includedParticipantGuesser
            ->getIncludedParticipantViews($sheet)
            ->shouldBeCalled()
            ->willReturn([new IncludedParticipantView($participantProduct, 2)]);

        $expectedParticipantProductViews = [
            new ParticipantProductView(
                1337,
                'Participant inclus',
                'Description Participant inclus',
                49,
                'EUR',
                Event::VAT_MODE_ET,
                2,
                0,
                true,
                0,
                false
            ),
        ];

        $participantProductViews = $handler->handle(new ParticipantProductViewQuery($sheet, $locale));

        $this->assertEquals($expectedParticipantProductViews, $participantProductViews);
    }

    /**
     * Test paying participant product
     */
    public function testPayingParticipantProductHandle()
    {
        $locale = 'fr';
        $date = new \DateTime();

        $event = EventFactory::createEvent();

        $plan = Product::createPlan($event, 'My plan', '', 99, 20, 0, 0);
        $participantProduct = Product::createParticipant($event, 'Paying participant product', 79, 20, 2);
        $this->setPropertyValue($participantProduct, 'id', 1337);
        $participantProductTranslationFr = new ProductTranslation(
            $participantProduct,
            $locale,
            'Participant supplémentaire payant',
            '',
            'Description du produit',
            '',
            ''
        );

        $translations = new ArrayCollection();
        $translations[$locale] = $participantProductTranslationFr;
        $participantProduct->setTranslations($translations);

        $package = new Package($event, 'Package', $date);
        $package->setPlans([$plan]);
        $package->setParticipants([$participantProduct]);
        $package->enable(true, true, false);

        $type = new Type($event);
        $type->setPackage($package);

        $sheet = new Sheet($event, $type, [], new User('user@vimeet.com', 'salt', 'password', 'fr'), $date);
        $participant = ParticipantFactory::create($sheet);
        $sheet->addParticipant($participant);
        $this->setPropertyValue($participant, 'id', 7331);

        $cart = $this->prophesize(Cart::class);
        $productParticipants = [
            7331 => $participantProduct,
        ];
        $this->cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart->reveal());
        $this->productByParticipantGetter
            ->getFromCart($cart->reveal())
            ->shouldBeCalled()
            ->willReturn($productParticipants)
        ;

        $handler = new ParticipantProductViewQueryHandler(
            $this->includedParticipantGuesser->reveal(),
            $this->productByParticipantGetter->reveal(),
            $this->cartManager->reveal()
        );

        $this->includedParticipantGuesser
            ->getIncludedParticipantViews($sheet)
            ->shouldBeCalled()
            ->willReturn(
                [
                    1337 => new IncludedParticipantView(
                        Product::createParticipant($event, 'My participant product', 49, 20, 2),
                        1
                    ),
                ]
            );

        $expectedParticipantProductViews = [
            new ParticipantProductView(
                1337,
                'Participant supplémentaire payant',
                'Description du produit',
                79,
                'EUR',
                Event::VAT_MODE_ET,
                2,
                1,
                true,
                0,
                false
            ),
        ];
        $participantProductViews = $handler->handle(new ParticipantProductViewQuery($sheet, $locale));

        $this->assertEquals($expectedParticipantProductViews, $participantProductViews);
    }

    public function testRemainingIncludedProductHandle()
    {
        $locale = 'fr';
        $date = new \DateTime();

        $event = EventFactory::createEvent();

        $plan = Product::createPlan($event, 'My plan', '', 99, 20, 0, 0);
        $participantProduct = Product::createParticipant($event, 'Paying participant product', 79, 20, 2);
        $this->setPropertyValue($participantProduct, 'id', 1337);
        $participantProductTranslationFr = new ProductTranslation(
            $participantProduct,
            $locale,
            'Participant supplémentaire payant',
            '',
            'Description du produit',
            '',
            ''
        );

        $translations = new ArrayCollection();
        $translations[$locale] = $participantProductTranslationFr;
        $participantProduct->setTranslations($translations);

        $package = new Package($event, 'Package', $date);
        $package->setPlans([$plan]);
        $package->setParticipants([$participantProduct]);
        $package->enable(true, true, false);

        $type = new Type($event);
        $type->setPackage($package);

        $sheet = new Sheet($event, $type, [], new User('user@vimeet.com', 'salt', 'password', 'fr'), $date);
        $participant = ParticipantFactory::create($sheet);
        $sheet->addParticipant($participant);
        $this->setPropertyValue($participant, 'id', 7331);

        $cart = $this->prophesize(Cart::class);
        $productParticipants = [
            7331 => $participantProduct,
        ];
        $this->cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart->reveal());
        $this->productByParticipantGetter
            ->getFromCart($cart->reveal())
            ->shouldBeCalled()
            ->willReturn($productParticipants)
        ;

        $handler = new ParticipantProductViewQueryHandler(
            $this->includedParticipantGuesser->reveal(),
            $this->productByParticipantGetter->reveal(),
            $this->cartManager->reveal()
        );

        $this->includedParticipantGuesser
            ->getIncludedParticipantViews($sheet)
            ->shouldBeCalled()
            ->willReturn(
                [
                    1337 => new IncludedParticipantView(
                        Product::createParticipant($event, 'My participant product', 49, 20, 2),
                        2
                    ),
                ]
            );

        $expectedParticipantProductViews = [
            new ParticipantProductView(
                1337,
                'Participant supplémentaire payant',
                'Description du produit',
                79,
                'EUR',
                Event::VAT_MODE_ET,
                2,
                2,
                true,
                1,
                true
            ),
        ];
        $participantProductViews = $handler->handle(new ParticipantProductViewQuery($sheet, $locale));

        $this->assertEquals($expectedParticipantProductViews, $participantProductViews);
    }

    /**
     * Test not buyable anymore
     */
    public function testNotBuyableParticipantProductHandle()
    {
        $locale = 'fr';
        $date = new \DateTime();

        $event = EventFactory::createEvent();

        $plan = Product::createPlan($event, 'My plan', '', 99, 20, 0, 0);
        $participantProduct = Product::createParticipant($event, 'Paying participant product', 79, 20, 2);
        $this->setPropertyValue($participantProduct, 'id', 1337);
        $participantProductTranslationFr = new ProductTranslation(
            $participantProduct,
            $locale,
            'Participant supplémentaire payant',
            '',
            'Description du produit',
            '',
            ''
        );

        $translations = new ArrayCollection();
        $translations[$locale] = $participantProductTranslationFr;
        $participantProduct->setTranslations($translations);

        $package = new Package($event, 'Package', $date);
        $package->setPlans([$plan]);
        $package->setParticipants([$participantProduct]);
        $package->enable(true, true, false);

        $type = new Type($event);
        $type->setPackage($package);

        $sheet = new Sheet($event, $type, [], new User('user@vimeet.com', 'salt', 'password', 'fr'), $date);
        $participant1 = ParticipantFactory::create($sheet);
        $participant2 = ParticipantFactory::create($sheet);
        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);
        $this->setPropertyValue($participant1, 'id', 7331);
        $this->setPropertyValue($participant2, 'id', 7332);

        $cart = $this->prophesize(Cart::class);
        $productParticipants = [
            7331 => $participantProduct,
            7332 => $participantProduct,
        ];
        $this->cartManager->getCart($sheet)->shouldBeCalled()->willReturn($cart->reveal());
        $this->productByParticipantGetter
            ->getFromCart($cart->reveal())
            ->shouldBeCalled()
            ->willReturn($productParticipants)
        ;

        $handler = new ParticipantProductViewQueryHandler(
            $this->includedParticipantGuesser->reveal(),
            $this->productByParticipantGetter->reveal(),
            $this->cartManager->reveal()
        );

        $this->includedParticipantGuesser
            ->getIncludedParticipantViews($sheet)
            ->shouldBeCalled()
            ->willReturn(
                [
                    1337 => new IncludedParticipantView(
                        Product::createParticipant($event, 'My participant product', 49, 20, 2),
                        1
                    ),
                ]
            );

        $expectedParticipantProductViews = [
            new ParticipantProductView(
                1337,
                'Participant supplémentaire payant',
                'Description du produit',
                79,
                'EUR',
                Event::VAT_MODE_ET,
                2,
                1,
                false,
                0,
                false
            ),
        ];
        $participantProductViews = $handler->handle(new ParticipantProductViewQuery($sheet, $locale));

        $this->assertEquals($expectedParticipantProductViews, $participantProductViews);
    }

    private function setPropertyValue($object, $field, $value)
    {
        $reflection = new \ReflectionClass(\get_class($object));
        $property = $reflection->getProperty($field);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }
}
