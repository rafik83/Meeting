<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Happening\HappeningCategoryView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CategoryViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event     = EventFactory::createEvent();
        $locale    = 'fr';
        $begin     = new \DateTime();
        $end       = new \DateTime();
        $category  = new Category($event, '', 1, '#aaa', '#bbb');

        $categoryTranslation = new Happening\CategoryTranslation($category, $locale, 'conference');
        $category->setTranslation($categoryTranslation);

        $happening = new Happening($event, $begin, $end, $category, []);

        // Expected
        $expectedCategoryView = new HappeningCategoryView('conference', '', '#aaa', '#bbb');

        $handler = new CategoryViewQueryHandler();
        $view = $handler->handle(new CategoryViewQuery($happening, $locale));

        $this->assertEquals($expectedCategoryView, $view);
    }
}
