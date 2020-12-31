<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $create = new Create($event);
        $create->event = $event;
        $create->title = 'promotionCodeTitle';
        $create->code = 'TESTCODE';
        $create->stock = 10;
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
        $promotionCodeRepository->add($promotionCode)->shouldBeCalled();

        $promotionCodeFactory = $this->prophesize(PromotionCodeFactory::class);
        $promotionCodeFactory
            ->create($event, 'promotionCodeTitle', 'TESTCODE', 10, null, [], [])
            ->shouldBeCalled()
            ->willReturn($promotionCode)
        ;

        $handler = new CreateHandler(
            $promotionCodeFactory->reveal(),
            $promotionCodeRepository->reveal()
        );

        $result = $handler->handle($create);
        $this->assertEquals($expectedResult, $result);
    }
}
