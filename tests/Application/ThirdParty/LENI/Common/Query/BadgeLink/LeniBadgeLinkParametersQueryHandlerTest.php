<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\Query\BadgeLink;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink\LeniBadgeLinkParametersQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink\LeniBadgeLinkParametersQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink\LeniBadgeLinkParametersView;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Exception\Event\ExtraParameter\BadFormattedExtraParameterValueException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class LeniBadgeLinkParametersQueryHandlerTest extends TestCase
{
    public function testBadFormattedValue(): void
    {
        $this->expectException(BadFormattedExtraParameterValueException::class);

        // prepare data
        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $leniBadgeLinkParametersQueryHandler = new LeniBadgeLinkParametersQueryHandler(
            $extraParameterRepository->reveal()
        );
        $event = $this->prophesize(Event::class);

        // prophecies
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()
            ->shouldBeCalled()
            ->willReturn('foo');

        $extraParameterRepository->findByEventAndType($event->reveal(), Type::TYPE_LENI_BADGE_LINK)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal());

        // run tests
        $leniBadgeLinkParametersQueryHandler->handle(new LeniBadgeLinkParametersQuery($event->reveal()));
    }

    public function test(): void
    {
        // prepare data
        $extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $leniBadgeLinkParametersQueryHandler = new LeniBadgeLinkParametersQueryHandler(
            $extraParameterRepository->reveal()
        );
        $event = $this->prophesize(Event::class);

        // prophecies
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $extraParameter->getValue()
            ->shouldBeCalled()
            ->willReturn('{"link": "https://localhost/leni/%s/view", "concerned_type_ids": [123]}');

        $extraParameterRepository->findByEventAndType($event->reveal(), Type::TYPE_LENI_BADGE_LINK)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal());

        // run tests
        $result = $leniBadgeLinkParametersQueryHandler->handle(new LeniBadgeLinkParametersQuery($event->reveal()));

        $expected = new LeniBadgeLinkParametersView('https://localhost/leni/%s/view', [123]);

        $this->assertEquals($expected, $result);
    }
}
