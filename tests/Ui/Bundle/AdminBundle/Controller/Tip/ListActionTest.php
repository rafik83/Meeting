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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class ListActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->twig = $this->prophesize(Environment::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);
        $request = $this->prophesize(Request::class);
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(false);
        $this->twig->render(Argument::any())->shouldNotBeCalled();

        $action = new ListAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal()
        );

        $action($request->reveal());
    }

    public function testInvoke()
    {
        $request = new Request(['page' => 12]);
        $response = new Response('Tip');
        $tipListView = $this->prophesize(PaginatedTipView::class);

        $command = new TipViewQuery(12);
        $this->authorizationCheckerAdapter->isGranted('ROLE_SUPER_ADMIN')->shouldBeCalled()->willReturn(true);
        $this->commandBus->handle($command)->shouldBeCalled()->willReturn($tipListView->reveal());
        $this->twig
            ->render(ListAction::TEMPLATE, ['tipListView' => $tipListView->reveal()])
            ->shouldBeCalled()
            ->willReturn('Tip')
        ;

        $action = new ListAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal()
        );

        $result = $action($request);

        $this->assertEquals($response, $result);
    }
}
