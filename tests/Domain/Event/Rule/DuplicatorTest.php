<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Rule;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Event\Rule\Duplicator;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

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

        $oldSeer    = new Type($event);
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($oldSeer, 2);

        $newSeer = new Type($event);

        $oldSeeable = new Category($event);
        $reflection = new \ReflectionClass(Category::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($oldSeeable, 6);

        $newSeeable = new Category($event);

        $oldRule      = new Rule($eventDuplicated, $oldSeer, $oldSeeable, ['what'], 1);
        $expectedRule = new Rule($event, $newSeer, $newSeeable, ['what'], 1);

        $ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $ruleRepository->getByEvent($eventDuplicated)->shouldBeCalled()->willReturn([$oldRule]);
        $ruleRepository->add($expectedRule)->shouldBeCalled();

        $duplicatorDataStorage = new DuplicatorDataStorage();
        $duplicatorDataStorage->types = [2 => $newSeer];
        $duplicatorDataStorage->categories = [6 => $newSeeable];

        $duplicator = new Duplicator($ruleRepository->reveal());
        $duplicator->duplicate($event, $duplicatorDataStorage);
    }
}
