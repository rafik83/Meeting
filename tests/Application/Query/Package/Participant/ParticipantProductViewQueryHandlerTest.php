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
        $date   = new \DateTime();

        $event = EventFactory::createEvent();

        $plan                                    = Product::createPlan($event, 'My plan', '', 99, 0, 0);
        $participantIncludedProduct              = Product::createParticipant($event, 'My participant product', 49, 2);
        $participantIncludedProductTranslationFr = new ProductTranslation(
            $participantIncludedProduct,
            $locale,
            'Participant inclus',
            '',
            '',
            '',
            ''
        );

        $translations          = new ArrayCollection();
        $translations[$locale] = $participantIncludedProductTranslationFr;
        $participantIncludedProduct->setTranslations($translations);

        $plan->includeProduct($participantIncludedProduct, 2);

        $package = new Package($event, 'Package', $date);
        $package->setPlans([$plan]);
        $package->enable(true, false, false);

        $type = new Type($event);
        $type->setPackage($package);

        $sheet       = new Sheet($event, $type, [], new User('user@vimeet.com', 'salt', 'password', 'fr'), $date);
        $participant = ParticipantFactory::create($sheet);
        $sheet->addParticipant($participant);

        $includedParticipantGuesser = $this->prophesize(IncludedParticipantGuesser::class);

        $handler = new ParticipantProductViewQueryHandler($includedParticipantGuesser->reveal());

        $includedParticipantGuesser->getIncludedParticipantView($sheet)->shouldBeCalled()->willReturn(
            new IncludedParticipantView($participantIncludedProduct, 2, 1)
        );

        $expectedParticipantProductView = new ParticipantProductView(
            'Participant inclus',
            49,
            'EUR',
            Event::VAT_MODE_ET,
            true // isIncluded = true
        );
        $participantProductView         = $handler->handle(new ParticipantProductViewQuery($sheet, $locale));

        $this->assertEquals($expectedParticipantProductView, $participantProductView);
    }

    /**
     * Test paying participant product
     */
    public function testPayingParticipantProductHandle()
    {
        $locale = 'fr';
        $date   = new \DateTime();

        $event = EventFactory::createEvent();

        $plan                            = Product::createPlan($event, 'My plan', '', 99, 0, 0);
        $participantProduct              = Product::createParticipant($event, 'Paying participant product', 79, 2);
        $participantProductTranslationFr = new ProductTranslation(
            $participantProduct,
            $locale,
            'Participant supplémentaire payant',
            '',
            '',
            '',
            ''
        );

        $translations          = new ArrayCollection();
        $translations[$locale] = $participantProductTranslationFr;
        $participantProduct->setTranslations($translations);

        $package = new Package($event, 'Package', $date);
        $package->setPlans([$plan]);
        $package->setParticipants([$participantProduct]);
        $package->enable(true, true, false);

        $type = new Type($event);
        $type->setPackage($package);

        $sheet       = new Sheet($event, $type, [], new User('user@vimeet.com', 'salt', 'password', 'fr'), $date);
        $participant = ParticipantFactory::create($sheet);
        $sheet->addParticipant($participant);

        $includedParticipantGuesser = $this->prophesize(IncludedParticipantGuesser::class);

        $handler = new ParticipantProductViewQueryHandler($includedParticipantGuesser->reveal());

        $includedParticipantGuesser->getIncludedParticipantView($sheet)->shouldBeCalled()->willReturn(
            new IncludedParticipantView(Product::createParticipant($event, 'My participant product', 49, 2), 1, 0)
        );

        $expectedParticipantProductView = new ParticipantProductView(
            'Participant supplémentaire payant',
            79,
            'EUR',
            Event::VAT_MODE_ET,
            false // isIncluded = false
        );
        $participantProductView         = $handler->handle(new ParticipantProductViewQuery($sheet, $locale));

        $this->assertEquals($expectedParticipantProductView, $participantProductView);
    }
}
