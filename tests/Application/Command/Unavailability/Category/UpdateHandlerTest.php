<?php

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Unavailability\Category\Update;
use Proximum\Vimeet\Application\Command\Unavailability\Category\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Repository\Unavailability\CategoryRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();

        // Starting model
        $category = new Category(
            $event,
            'picto',
            'title',
            '#leftColor',
            '#rightColor'
        );

        // Expected
        $expectedCategory = new Category(
            $event,
            'picto2',
            'title2',
            '#123123',
            '#456456'
        );

        // Mock
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository->update($expectedCategory)->shouldBeCalled();

        $handler            = new UpdateHandler($categoryRepository->reveal());
        $update             = new Update($category);
        $update->picto      = 'picto2';
        $update->title      = 'title2';
        $update->leftColor  = '#123123';
        $update->rightColor = '#456456';

        $handler->handle($update);
    }
}
