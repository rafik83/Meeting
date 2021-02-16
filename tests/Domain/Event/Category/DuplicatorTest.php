<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\Category\Duplicator;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\CategoryTranslation;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $duplicatedEvent = EventFactory::createEvent('duplicated event');
        $event           = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $duplicatedEvent
        );

        $newType    = new Type($event);
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($newType, 9);

        $oldType    = new Type($duplicatedEvent);
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($oldType, 2);

        $expectedCategory = new Category($event);
        $oldCategory      = new Category($duplicatedEvent);

        $oldCategory->getTranslations()->set('fr', new CategoryTranslation($oldCategory, 'fr', 'title fr'));
        $oldCategory->getTranslations()->set('en', new CategoryTranslation($oldCategory, 'en', 'title en'));
        $reflection = new \ReflectionClass(Category::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($oldCategory, 5);

        $expectedCategory->getTranslations()->set('fr', new CategoryTranslation($expectedCategory, 'fr', 'title fr'));
        $expectedCategory->getTranslations()->set('en', new CategoryTranslation($expectedCategory, 'en', 'title en'));

        $expectedCategory->setType($newType, $newType->getId());
        $oldCategory->setType($oldType, 2);

        $categoriesRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoriesRepository
            ->getCategoriesByEvent($duplicatedEvent)
            ->shouldBeCalled()
            ->willReturn([$oldCategory]);

        $categoriesRepository->add($expectedCategory)->shouldBeCalled();

        $duplicationDataStorage = new DuplicatorDataStorage();
        $duplicationDataStorage->types = [2 => $newType];

        $duplicator = new Duplicator($categoriesRepository->reveal());
        $result = $duplicator->duplicate($event, $duplicationDataStorage);

        $resultCategory = $result->categories[5];
        $this->assertEquals($resultCategory, $expectedCategory);
        $this->assertEquals($resultCategory->getTypes()[9], $newType);
    }
}
