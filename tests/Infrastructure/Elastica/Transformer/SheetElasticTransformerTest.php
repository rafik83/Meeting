<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Elastica\Transformer;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Elastica\Transformer\SheetElasticTransformer;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetElasticTransformerTest extends TestCase
{
    public function testTransform()
    {
        $locale = 'fr';

        $event = EventFactory::createEvent();
        $event->setLocales(['fr']);

        $sheet = SheetFactory::create($event);

        $registrationTemplateData = new TemplateData('type', [], 'fr', 'fr');
        $sheetTemplateData = new TemplateData('type', [], 'fr', 'fr');

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser->guessSheetTitle($sheet, $locale)->shouldBeCalled();

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);

        $cartRowRepository = $this->prophesize(CartRowRepositoryInterface::class);
        $cartRowRepository->findBySheet($sheet)->shouldBeCalled();

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository->hasParticipationsBySheet($sheet)->shouldBeCalled();

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->hasRequestSentBySheet($sheet)->shouldBeCalled();
        $requestRepository->hasPendingPropositionReceivedBySheet($sheet)->shouldBeCalled();

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory
            ->createRegistrationFromSheet($sheet, $locale)
            ->shouldBeCalled()
            ->willReturn($registrationTemplateData);
        $templateDataFactory->createFromSheet($sheet, $locale)->shouldBeCalled()->willReturn($sheetTemplateData);

        $balance = $this->prophesize(Balance::class);
        $balance->getNotCancelledOrderVatViews($sheet)->shouldBeCalled();
        $balance->getRemainingToPay($sheet)->shouldBeCalled();

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->hasScheduledMeeting($sheet)->shouldBeCalled();

        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $invoiceRepository->hasInvoice($sheet)->shouldBeCalled();

        $transformer = new SheetElasticTransformer(
            $sheetInfoGuesser->reveal(),
            $participantInfoGuesser->reveal(),
            $cartRowRepository->reveal(),
            $happeningParticipationRepository->reveal(),
            $requestRepository->reveal(),
            $templateDataFactory->reveal(),
            $balance->reveal(),
            $meetingRepository->reveal(),
            $invoiceRepository->reveal()
        );

        $transformer->transform($sheet, []);
    }
}
