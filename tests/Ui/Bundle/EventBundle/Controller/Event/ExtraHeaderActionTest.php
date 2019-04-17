<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Controller\Event;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Event\ExtraParameter\GetExtraParameterQuery;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Event\ExtraHeaderAction;
use PHPUnit\Framework\TestCase;

class ExtraHeaderActionTest extends TestCase
{
    public function testExtraHeaderAction()
    {
        $trackingCode = '<script>some javascript;</script>';

        $event = $this->prophesize(Event::class);
        $trackingCodeExtraParameter = new Event\ExtraParameter(
            $event->reveal(),
            Type::TYPE_TRACKING_CODE,
            'tracking',
            $trackingCode,
            new \DateTime()
        );

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus
            ->handle(new GetExtraParameterQuery($event->reveal(), Type::TYPE_TRACKING_CODE))
            ->shouldBeCalled()
            ->willReturn($trackingCodeExtraParameter)
        ;

        $extraHeaderAction = new ExtraHeaderAction($queryBus->reveal());
        $response = $extraHeaderAction($event->reveal());
        $this->assertEquals($trackingCode, $response->getContent());
    }

    public function testExtraHeaderActionReturnsEmptyString()
    {
        $event = $this->prophesize(Event::class);
        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus
            ->handle(new GetExtraParameterQuery($event->reveal(), Type::TYPE_TRACKING_CODE))
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $extraHeaderAction = new ExtraHeaderAction($queryBus->reveal());
        $response = $extraHeaderAction($event->reveal());
        $this->assertEquals('', $response->getContent());
    }
}
