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

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();

        // Expected
        $promotionCode = new PromotionCode(
            $event,
            'promotionCodeTitle',
            'TESTCODE',
            10
        );

        $update = new Update($promotionCode);

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $uniqueCodeChecker       = $this->prophesize(UniqueCodeChecker::class);

        $promotionCodeRepository->set($promotionCode)->shouldBeCalled();
        $uniqueCodeChecker->hasUniqueCode($promotionCode)->shouldBeCalled()->willReturn(true);

        $handler = new UpdateHandler(
            $promotionCodeRepository->reveal(),
            $uniqueCodeChecker->reveal()
        );

        $handler->handle($update);
    }
}
