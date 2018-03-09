<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $create = new Create($event);
        $create->event        = $event;
        $create->title        = 'promotionCodeTitle';
        $create->code         = 'TESTCODE';
        $create->stock        = 10;
        $create->translations = [];

        // Expected
        $promotionCode = new PromotionCode(
            $event,
            'promotionCodeTitle',
            'TESTCODE',
            10
        );

        $expectedResult = new CreateResult($promotionCode);

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $uniqueCodeChecker       = $this->prophesize(UniqueCodeChecker::class);

        $promotionCodeRepository->add($promotionCode)->shouldBeCalled();
        $uniqueCodeChecker->hasUniqueCode($promotionCode)->shouldBeCalled()->willReturn(true);

        $handler = new CreateHandler(
            $promotionCodeRepository->reveal(),
            $uniqueCodeChecker->reveal()
        );

        $result = $handler->handle($create);

        $this->assertEquals($expectedResult, $result);
    }
}
