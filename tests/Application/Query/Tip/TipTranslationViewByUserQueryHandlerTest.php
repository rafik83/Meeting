<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Tip;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewByUserQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewByUserQueryHandler;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class TipTranslationViewByUserQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);

        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());

        $onConfirmationPhoneContext = TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE;

        $tipTranslationViewQueryHandler = $this->prophesize(TipTranslationViewQueryHandler::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $tipTranslationViewByUserQueryHandler = new TipTranslationViewByUserQueryHandler(
            $tipTranslationViewQueryHandler->reveal(),
            $sheetRepository->reveal()
        );

        $sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet])
        ;

        $tipTranslationViewQueryHandler
            ->handle(new TipTranslationViewQuery($sheet->reveal(), $user->reveal(), $onConfirmationPhoneContext, 'fr'))
            ->shouldBeCalled()
        ;

        $tipTranslationViewByUserQueryHandler->handle(
            new TipTranslationViewByUserQuery(
                $event->reveal(),
                $user->reveal(),
                $onConfirmationPhoneContext,
                'fr'
            )
        );
    }

    public function testSheetNotFoundExceptionHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $onConfirmationPhoneContext = TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE;

        $tipTranslationViewQueryHandler = $this->prophesize(TipTranslationViewQueryHandler::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $tipTranslationViewByUserQueryHandler = new TipTranslationViewByUserQueryHandler(
            $tipTranslationViewQueryHandler->reveal(),
            $sheetRepository->reveal()
        );

        $sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->expectException(SheetNotFoundException::class);

        $tipTranslationViewByUserQueryHandler->handle(
            new TipTranslationViewByUserQuery(
                $event->reveal(),
                $user->reveal(),
                $onConfirmationPhoneContext,
                'fr'
            )
        );
    }
}
