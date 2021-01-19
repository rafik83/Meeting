<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Happening\Category\Create;
use Proximum\Vimeet\Application\Command\Happening\Category\CreateHandler;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $expectedCategory     = new Category($event, 'picto1', 3, '#AABB56', '#123456');
        $expectedTranslation1 = new CategoryTranslation($expectedCategory, 'fr', 'truc');
        $expectedTranslation2 = new CategoryTranslation($expectedCategory, 'en', 'trac');
        $expectedCategory->setTranslation($expectedTranslation1);
        $expectedCategory->setTranslation($expectedTranslation2);

        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository->add($expectedCategory)->shouldBeCalled();

        $create  = new Create($event);
        $create->picto = 'picto1';
        $create->rank  = 3;
        $create->translations = [
            'fr' => ['title' => 'truc'],
            'en' => ['title' => 'trac'],
        ];
        $create->leftColor  = '#AABB56';
        $create->rightColor = '#123456';

        $handler = new CreateHandler($categoryRepository->reveal());
        $handler->handle($create);
    }
}
