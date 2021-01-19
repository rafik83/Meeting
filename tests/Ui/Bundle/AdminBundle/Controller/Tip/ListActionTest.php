<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Query\Tip\TipViewQuery;
use Proximum\Vimeet\Application\View\Tip\PaginatedTipView;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\ListAction;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $engine;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->engine = $this->prophesize(EngineInterface::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $request = $this->prophesize(Request::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(false);
        $this->engine->renderResponse(Argument::any())->shouldNotBeCalled();

        $action = new ListAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->engine->reveal()
        );

        $action($request->reveal());
    }

    public function testInvoke()
    {
        $request = new Request(['page' => 12]);
        $response = $this->prophesize(Response::class);
        $tipListView = $this->prophesize(PaginatedTipView::class);

        $command = new TipViewQuery(12);
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(true);
        $this->commandBus->handle($command)->shouldBeCalled()->willReturn($tipListView->reveal());
        $this->engine
            ->renderResponse(ListAction::TEMPLATE, ['tipListView' => $tipListView->reveal()])
            ->shouldBeCalled()
            ->willReturn($response->reveal())
        ;

        $action = new ListAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->engine->reveal()
        );

        $result = $action($request);

        $this->assertEquals($response->reveal(), $result);
    }
}
