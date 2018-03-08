<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\PromotionCode;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\PromotionCode\Create;
use Proximum\Vimeet\Application\Command\PromotionCode\CreateHandler;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Domain\Promotion\Exception\NonUniqueCodeException;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AbstractCommandHandlerTest extends TestCase
{
    public function testNonUniqueCodeException()
    {
        $this->expectException(NonUniqueCodeException::class);

        $event = EventFactory::createEvent();

        // Expected
        $promotionCode = new PromotionCode(
            $event,
            'promotionCodeTitle',
            'TESTCODE',
            10
        );

        $create = new Create($event);
        $create->event        = $event;
        $create->title        = 'promotionCodeTitle';
        $create->code         = 'TESTCODE';
        $create->stock        = 10;
        $create->translations = [];

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $uniqueCodeChecker       = $this->prophesize(UniqueCodeChecker::class);

        $uniqueCodeChecker->hasUniqueCode($promotionCode)->shouldBeCalled()->willReturn(false);

        $handler = new CreateHandler(
            $promotionCodeRepository->reveal(),
            $uniqueCodeChecker->reveal()
        );

        $handler->handle($create);
    }

    public function testTranslate()
    {
        $event = EventFactory::createEvent();

        $translations = [
            'fr' => [
                'label'  => 'labelTest',
                'description' => 'descriptionTest',
            ],
            'en' => [
                'label'  => 'labelTestEn',
                'description' => 'descriptionTestEn',
            ],
        ];

        $create = new Create($event);
        $create->event        = $event;
        $create->title        = 'promotionCodeTitle';
        $create->code         = 'TESTCODE';
        $create->stock        = 10;
        $create->translations = $translations;

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);

        $uniqueCodeChecker = $this->getMockBuilder(UniqueCodeChecker::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $uniqueCodeChecker
            ->expects($this->once())
            ->method('hasUniqueCode')
            ->withAnyParameters()
            ->willReturn(true)
        ;

        $handler = new CreateHandler(
            $promotionCodeRepository->reveal(),
            $uniqueCodeChecker
        );

        $result = $handler->handle($create);
        $this->assertEquals($translations, $result->promotionCode->getTranslationsData());
    }
}
