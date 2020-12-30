<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip\Event;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Query\Tip\Event\PaginatedTipViewQuery;
use Proximum\Vimeet\Application\View\Tip\PaginatedTipView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\Event\ListAction;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $request;

    public function setUp()
    {
        $this->request = new Request(['page' => 12]);
        $this->request->setLocale('fr');
        $this->event = $this->prophesize(Event::class);
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->engine = $this->prophesize(EngineInterface::class);
    }

    public function testAccessDeniedRole()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();

        $action = new ListAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->engine->reveal()
        );
        $action($this->request, $this->event->reveal());
    }

    public function testAccessDeniedEventPermission()
    {
        $this->expectException(AccessDeniedException::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();

        $action = new ListAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->engine->reveal()
        );
        $action($this->request, $this->event->reveal());
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->authorizationCheckerAdapter
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->event->getAvailableLocale('fr')->willReturn('de');

        $command = new PaginatedTipViewQuery($this->event->reveal(), 12);
        $tipListView = $this->prophesize(PaginatedTipView::class);
        $this->commandBus->handle($command)->shouldBeCalled()->willReturn($tipListView->reveal());
        $response = new Response();
        $this->engine
            ->renderResponse(ListAction::TEMPLATE, [
                'event' => $this->event->reveal(),
                'tipListView' => $tipListView->reveal(),
                'locale' => 'de',
            ])
            ->shouldBeCalled()
            ->willReturn($response)
        ;

        $action = new ListAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal(),
            $this->engine->reveal()
        );
        $result = $action($this->request, $this->event->reveal());

        $this->assertEquals($response, $result);
    }
}
