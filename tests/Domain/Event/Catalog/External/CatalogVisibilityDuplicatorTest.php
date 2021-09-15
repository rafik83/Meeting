<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Catalog\External;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\Catalog\External\CatalogVisibilityDuplicator;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CatalogVisibilityDuplicatorTest extends TestCase
{
    public function testDuplicate()
    {
        $eventDuplicated = EventFactory::createEvent('event duplicated');
        $eventDuplicated->setExternalCatalog(true);
        $event = EventFactory::createEvent(
            'event',
            EventFactory::FALLBACK_LOCALE_DEFAULT,
            ['fr', 'en'],
            Event::VAT_MODE_ET,
            $eventDuplicated
        );
        $event->setExternalCatalog(true);

        $oldType    = new Type($event);
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($oldType, 1);

        $newType = new Type($event);

        $oldCategory = new Category($event);
        $reflection  = new \ReflectionClass(Category::class);
        $property    = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($oldCategory, 1);

        $newCategory = new Category($event);

        $catalogVisibility = new CatalogVisibility($eventDuplicated);
        $catalogVisibility->setType($oldType);
        $catalogVisibility->setCategory($oldCategory);

        $expectedCatalogVisibility = new CatalogVisibility($event);
        $expectedCatalogVisibility->updateTypesAndCategories([$newType], [$newCategory]);

        $catalogVisibilityRepository = $this->prophesize(CatalogVisibilityRepositoryInterface::class);
        $catalogVisibilityRepository->add($expectedCatalogVisibility)->shouldBeCalled();
        $catalogVisibilityRepository
            ->getByEvent($eventDuplicated)
            ->shouldBeCalled()
            ->willReturn($catalogVisibility);

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($event)->shouldBeCalled();

        $duplicatorDataStorage             = new DuplicatorDataStorage();
        $duplicatorDataStorage->types      = [1 => $newType];
        $duplicatorDataStorage->categories = [1 => $newCategory];

        (new CatalogVisibilityDuplicator(
            $catalogVisibilityRepository->reveal(),
            $eventRepository->reveal()
        ))->duplicate($event, $duplicatorDataStorage);
    }
}
