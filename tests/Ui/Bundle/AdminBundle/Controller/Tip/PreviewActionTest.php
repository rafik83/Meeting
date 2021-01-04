<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Tip;

use League\Tactician\CommandBus;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Query\Tip\Event\PreviewTipViewQuery;
use Proximum\Vimeet\Application\View\Tip\Event\PreviewTipView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Tip\PreviewAction;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PreviewActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $tip;

    public function setUp()
    {
        $this->tip = $this->prophesize(Tip::class);
        $this->commandBus = $this->prophesize(CommandBus::class);
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
    }

    public function testAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(false);
        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();

        $action = new PreviewAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action($this->tip->reveal(), 'fr');
    }

    public function testAccessDeniedHasEvent()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->tip->hasEvent()->willReturn(true);

        $this->commandBus->handle(Argument::any())->shouldNotBeCalled();

        $action = new PreviewAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $action($this->tip->reveal(), 'fr');
    }

    public function testInvoke()
    {
        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $this->tip->hasEvent()->willReturn(false);
        $this->tip->getPagesTranslations()->willReturn([]);
        $view = $this->prophesize(PreviewTipView::class);

        $this->commandBus
            ->handle(new PreviewTipViewQuery($this->tip->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($view->reveal())
        ;

        $action = new PreviewAction(
            $this->commandBus->reveal(),
            $this->authorizationCheckerAdapter->reveal()
        );

        $result = $action($this->tip->reveal(), 'fr');

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(json_encode($view->reveal(), true), $result->getContent());
    }
}
