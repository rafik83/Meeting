<?php

namespace Application\Query\Catalog\External;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityMessageQuery;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityMessageQueryHandler;
use Proximum\Vimeet\Application\View\CatalogVisibility\MessageView;
use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Repository\CatalogVisibilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CatalogVisibilityMessageQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $catalogVisibilityRepository;

    /** @var CatalogVisibilityMessageQueryHandler */
    private $catalogVisibilityMessageHandler;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->catalogVisibilityRepository = $this->prophesize(CatalogVisibilityRepositoryInterface::class);
        $this->catalogVisibilityMessageHandler = new CatalogVisibilityMessageQueryHandler(
            $this->catalogVisibilityRepository->reveal()
        );
        $this->event = EventFactory::createEvent();
    }

    public function testWithNoMessageHandle()
    {
        $expected = null;
        $catalogVisiblity = new CatalogVisibility($this->event, false);

        $this->catalogVisibilityRepository
            ->getByEvent($this->event)
            ->shouldBeCalled()
            ->willReturn($catalogVisiblity);

        $result = $this->catalogVisibilityMessageHandler->handle(
            new CatalogVisibilityMessageQuery($this->event, 'fr')
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $expected = new MessageView('title', 'content');
        $catalogVisibility = new CatalogVisibility($this->event, true);
        $catalogVisibility->translate('title', 'content', 'fr');

        $this->catalogVisibilityRepository
            ->getByEvent($this->event)
            ->shouldBeCalled()
            ->willReturn($catalogVisibility);

        $result = $this->catalogVisibilityMessageHandler->handle(
            new CatalogVisibilityMessageQuery($this->event, 'fr')
        );

        $this->assertEquals($expected, $result);
    }
}
