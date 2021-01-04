<?php

namespace Proximum\Vimeet\Tests\Application\Command\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Category\Create;
use Proximum\Vimeet\Application\Command\Category\CreateHandler;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');

        //Expected
        $category = new Category($event);
        $category->getTranslations()->set(
            'fr',
            new CategoryTranslation($category, 'fr', 'Ma Catégorie')
        );

        //Command
        $create = new Create($event);
        $create->translations = ['fr' => ['title' => 'Ma Catégorie']];

        //Mock
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $categoryRepository->add($category)->shouldBeCalled();
        $jobQueue->indexSheetsByTypes($create->types)->shouldBeCalled();

        //Handler
        $handler = new CreateHandler(
            $categoryRepository->reveal(),
            $typeRepository->reveal(),
            $jobQueue->reveal()
        );

        $handler->handle($create);
    }

    public function testHandleException()
    {
        $this->expectException('Exception');

        //Context
        $event = EventFactory::createEvent();
        $event->setLocales(['fr'], 'fr');

        //Expected
        $category = new Category($event);

        //Command
        $create = new Create($event);
        $create->types = ['test'];

        //Mock
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $categoryRepository->add($category);

        $typeRepository->getTypesByEvent($event)->willReturn([]);

        $jobQueue = $this->prophesize(JobQueueInterface::class);
        $jobQueue->indexSheetsByTypes($create->types)->shouldNotBeCalled();

        //Handler
        $handler = new CreateHandler(
            $categoryRepository->reveal(),
            $typeRepository->reveal(),
            $jobQueue->reveal()
        );
        $handler->handle($create);
    }
}
