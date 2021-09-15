<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\StaticFormulation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Intention\IntentionType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\StaticFormulation\DeleteAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class DeleteActionTest extends TestCase
{
    public function testInvoke()
    {
        $request = new Request(['_token' => '1234567890']);
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(12);
        $staticFormulation = $this->prophesize(StaticFormulation::class);
        $staticFormulation->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event->reveal())->shouldBeCalled()->willReturn(true);

        $urlGenerator = $this->prophesize(UrlGenerator::class);
        $staticFormulationRepository = $this->prophesize(StaticFormulationRepositoryInterface::class);
        $csrfTokenManager = $this->prophesize(CsrfTokenManager::class);
        $flashBag = $this->prophesize(FlashBagInterface::class);

        $csrfTokenManager
            ->isTokenValid(new CsrfToken(IntentionType::INTENTION_REMOVE_STATIC_FORMULATION, '1234567890'))
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $staticFormulationRepository->remove($staticFormulation->reveal())->shouldBeCalled();
        $flashBag->add('success', 'flash.admin.staticFormulation.removed.success')->shouldBeCalled();
        $urlGenerator
            ->generate('admin_event_static_formulation_list', ['event' => 12])
            ->shouldBeCalled()
            ->willReturn('/fr/event/1/static-formulation')
        ;

        $action = new DeleteAction(
            $authorizationChecker->reveal(),
            $urlGenerator->reveal(),
            $staticFormulationRepository->reveal(),
            $csrfTokenManager->reveal(),
            $flashBag->reveal()
        );
        $result = $action($request, $event->reveal(), $staticFormulation->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/fr/event/1/static-formulation', $result->getTargetUrl());
    }

    public function testInvokeTokenInvalid()
    {
        $this->expectException(AccessDeniedException::class);

        $request = new Request(['_token' => '1234567890']);
        $event = $this->prophesize(Event::class);
        $staticFormulation = $this->prophesize(StaticFormulation::class);
        $staticFormulation->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')->shouldBeCalled()->willReturn(true);
        $authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event->reveal())->shouldBeCalled()->willReturn(true);

        $urlGenerator = $this->prophesize(UrlGenerator::class);
        $staticFormulationRepository = $this->prophesize(StaticFormulationRepositoryInterface::class);
        $csrfTokenManager = $this->prophesize(CsrfTokenManager::class);
        $flashBag = $this->prophesize(FlashBagInterface::class);

        $csrfTokenManager
            ->isTokenValid(new CsrfToken(IntentionType::INTENTION_REMOVE_STATIC_FORMULATION, '1234567890'))
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $staticFormulationRepository->remove($staticFormulation->reveal())->shouldNotBeCalled();
        $flashBag->add('success', 'flash.admin.staticFormulation.removed.success')->shouldNotBeCalled();
        $urlGenerator
            ->generate('admin_event_static_formulation_list', ['event' => 12])
            ->shouldNotBeCalled()
            ->willReturn('/fr/event/1/static-formulation')
        ;

        $action = new DeleteAction(
            $authorizationChecker->reveal(),
            $urlGenerator->reveal(),
            $staticFormulationRepository->reveal(),
            $csrfTokenManager->reveal(),
            $flashBag->reveal()
        );
        $action($request, $event->reveal(), $staticFormulation->reveal());
    }
}
