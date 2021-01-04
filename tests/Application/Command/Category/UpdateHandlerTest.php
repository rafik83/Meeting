<?php

namespace Proximum\Vimeet\Tests\Application\Command\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Category\Update;
use Proximum\Vimeet\Application\Command\Category\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\CategoryTranslation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    /**
     * Category contains Types 1 and 2 and will be updated to contain Types 2 and 4
     */
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');

        $type1 = new Type($event);
        $this->setTypeId($type1, 1);

        $type2 = new Type($event);
        $this->setTypeId($type2, 2);

        $type3 = new Type($event);
        $this->setTypeId($type3, 3);

        $type4 = new Type($event);
        $this->setTypeId($type4, 4);

        $category = new Category($event);
        $category->getTranslations()->set(
            'fr',
            new CategoryTranslation($category, 'fr', 'Ma Catégorie')
        );
        $category->addType($type1);
        $category->addType($type2);

        $update = new Update($category);
        $update->types = [2, 4];
        $update->translations = ['fr' => ['title' => 'Ma Catégorie']];

        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $categoryRepository->set($category)->shouldBeCalled();
        $jobQueue->indexSheetsByTypes([1, 2, 4])->shouldBeCalled();

        $typeRepository
            ->getTypesByEvent($update->event)
            ->shouldBeCalled()
            ->willReturn([1 => $type1, 2 => $type2, 3 => $type3, 4 => $type4]);

        //Handler
        $handler = new UpdateHandler($categoryRepository->reveal(), $typeRepository->reveal(), $jobQueue->reveal());
        $handler->handle($update);
    }

    /**
     * No Types changed
     */
    public function testNoTypesChangedHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');

        $type1 = new Type($event);
        $this->setTypeId($type1, 1);

        $category = new Category($event);
        $category->getTranslations()->set(
            'fr',
            new CategoryTranslation($category, 'fr', 'Ma Catégorie')
        );
        $category->addType($type1);

        $update = new Update($category);
        $update->types = [1];
        $update->translations = ['fr' => ['title' => 'Ma Catégorie']];

        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $categoryRepository->set($category)->shouldBeCalled();
        $jobQueue->indexSheetsByTypes()->shouldNotBeCalled();

        $typeRepository
            ->getTypesByEvent($update->event)
            ->shouldBeCalled()
            ->willReturn([1 => $type1]);

        //Handler
        $handler = new UpdateHandler($categoryRepository->reveal(), $typeRepository->reveal(), $jobQueue->reveal());
        $handler->handle($update);
    }

    private function setTypeId($type, $id)
    {
        $reflection = new \ReflectionClass(Type::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($type, $id);
    }
}
