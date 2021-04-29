<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\ExtraData;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdate;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;

class AddOrUpdateHandlerTest extends TestCase
{
    public function testAddHandle()
    {
        $dateTime = new \DateTime();
        $event = $this->prophesize(Event::class);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEvent($event->reveal(), 'data-name')
            ->willReturn(null)
        ;

        $expectedExtraData = new ExtraData($event->reveal(), 'data-name', 'data-value', $dateTime);
        $extraDataRepository->add($expectedExtraData)->shouldBeCalled();

        $addOrUpdateHandler = new AddOrUpdateHandler($extraDataRepository->reveal(), $dateTime);
        $result = $addOrUpdateHandler->handle(new AddOrUpdate($event->reveal(), 'data-name', 'data-value'));

        $this->assertEquals($expectedExtraData, $result);
    }

    public function testUpdateHandle()
    {
        $past = new \DateTime('2017-01-01');
        $now = new \DateTime('2018-01-01');

        $event = $this->prophesize(Event::class);

        $extraData = new ExtraData($event->reveal(), 'data-name', 'previous-value', $past);

        $expectedExtraData = clone $extraData;
        $expectedExtraData->update('data-value', $now);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEvent($event->reveal(), 'data-name')
            ->willReturn($extraData)
        ;

        $extraDataRepository->set($expectedExtraData)->shouldBeCalled();

        $addOrUpdateHandler = new AddOrUpdateHandler($extraDataRepository->reveal(), $now);
        $result = $addOrUpdateHandler->handle(new AddOrUpdate($event->reveal(), 'data-name', 'data-value'));

        $this->assertEquals($expectedExtraData, $result);
    }
}
