<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Package\Participant;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Participant\ParticipantProductViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
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
use PHPUnit\Framework\TestCase;

class ParticipantProductViewQueryHandlerTest extends TestCase
{
    /**
     * Test included participant remaining > 0
     */
    public function testRemainingQuantityHandle()
    {
        $locale = 'fr';
        $date = new \DateTime();

        $event = EventFactory::createEvent();

        $plan = Product::createPlan($event, 'My plan', '', 99, 0, 0);
        $participantProduct = Product::createParticipant($event, 'My participant product', 49, 2);
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

        $includedParticipantGuesser = $this->prophesize(IncludedParticipantGuesser::class);

        $handler = new ParticipantProductViewQueryHandler($includedParticipantGuesser->reveal());

        $includedParticipantGuesser->getIncludedParticipantViews($sheet)->shouldBeCalled()->willReturn(
            [new IncludedParticipantView($participantProduct, 2, 1)]
        );

        $expectedParticipantProductViews = [
            new ParticipantProductView(
                1337,
                'Participant inclus',
                'Description Participant inclus',
                49,
                'EUR',
                Event::VAT_MODE_ET,
                2,
                0
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

        $plan = Product::createPlan($event, 'My plan', '', 99, 0, 0);
        $participantProduct = Product::createParticipant($event, 'Paying participant product', 79, 2);
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

        $includedParticipantGuesser = $this->prophesize(IncludedParticipantGuesser::class);

        $handler = new ParticipantProductViewQueryHandler($includedParticipantGuesser->reveal());

        $includedParticipantGuesser->getIncludedParticipantViews($sheet)->shouldBeCalled()->willReturn(
            [new IncludedParticipantView(Product::createParticipant($event, 'My participant product', 49, 2), 1, 0)]
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
                0
            ),
        ];
        $participantProductViews = $handler->handle(new ParticipantProductViewQuery($sheet, $locale));

        $this->assertEquals($expectedParticipantProductViews, $participantProductViews);
    }

    private function setPropertyValue($object, $field, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($field);
        $property->setAccessible(true);
        $property->setValue($object, $value);
        $property->setAccessible(false);
    }
}
