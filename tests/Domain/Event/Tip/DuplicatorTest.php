<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Tip;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\Tip\Duplicator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\TipFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $eventDuplicated = EventFactory::createEvent('event duplicated');
        $event           = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );

        $oldType = new Type($event);
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($oldType, 1);

        $newType = new Type($event);

        $oldTip = TipFactory::createTip('tip title');
        $oldTip->setType($oldType);

        $expectedTip = TipFactory::createTip('tip title', $event);
        $expectedTip->setType($newType);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $tipRepository->getByEvent($eventDuplicated)->shouldBeCalled()->willReturn([$oldTip]);
        $tipRepository->add(Argument::that(function (Tip $newTip) {
            return true;
        }))->shouldBeCalled();

        $duplicatorDataStorage = new DuplicatorDataStorage();
        $duplicatorDataStorage->types = [1 => $newType];

        $duplicator = new Duplicator($tipRepository->reveal(), new \DateTime());
        $duplicator->duplicate($event, $duplicatorDataStorage);
    }
}
