<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Category;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Happening\Category\Update;
use Proximum\Vimeet\Application\Command\Happening\Category\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Context
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        // Current
        $category     = new Category($event, 'picto1', 2, '#123456', '#AA3456');
        $translation1 = new CategoryTranslation($category, 'fr', 'truc');
        $translation2 = new CategoryTranslation($category, 'en', 'trac');
        $category->setTranslation($translation1);
        $category->setTranslation($translation2);

        // Expected
        $expectedCategory     = new Category($event, 'picto2', 3, '#654321', '#BB4321');
        $expectedTranslation1 = new CategoryTranslation($expectedCategory, 'fr', 'troc');
        $expectedTranslation2 = new CategoryTranslation($expectedCategory, 'en', 'trec');
        $expectedCategory->setTranslation($expectedTranslation1);
        $expectedCategory->setTranslation($expectedTranslation2);

        // Mock
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository->set(Argument::that(function (Category $category) use ($expectedCategory) {
            if ($category->getRank() !== $expectedCategory->getRank()) {
                return false;
            }

            // Avoid: Nesting level too deep
            if ($category->getTitle('fr') !== $expectedCategory->getTitle('fr')) {
                return false;
            }

            if ($category->getTitle('en') !== $expectedCategory->getTitle('en')) {
                return false;
            }

            return true;
        }))->shouldBeCalled();

        // Command
        $update        = new Update($category);
        $update->picto = 'picto2';
        $update->rank  = 3;
        $update->translations = [
            'fr' => ['title' => 'troc'],
            'en' => ['title' => 'trec'],
        ];
        $update->leftColor  = '#654321';
        $update->rightColor = '#BB4321';

        $handler = new UpdateHandler($categoryRepository->reveal());
        $handler->handle($update);
    }
}
