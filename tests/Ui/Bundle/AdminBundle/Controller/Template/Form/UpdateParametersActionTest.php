<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Template\Form;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Template\Form\UpdateParameters;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Form\UpdateParametersAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Form\UpdateParametersType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class UpdateParametersActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationChecker;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy */
    private $twig;

    /** @var ObjectProphecy */
    private $flashBag;

    /** @var ObjectProphecy */
    private $formFactory;

    /** @var ObjectProphecy */
    private $router;

    /** @var UpdateParametersAction */
    private $updateParametersAction;

    /** @var ObjectProphecy */
    private $request;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $formTemplate;

    public function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->event = $this->prophesize(Event::class);
        $this->formTemplate = $this->prophesize(FormTemplate::class);

        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->authorizationChecker
            ->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->authorizationChecker
            ->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);

        $this->updateParametersAction = new UpdateParametersAction(
            $this->authorizationChecker->reveal(),
            $this->commandBus->reveal(),
            $this->twig->reveal(),
            $this->flashBag->reveal(),
            $this->formFactory->reveal(),
            $this->router->reveal()
        );

    }

    public function test_form_template_is_not_in_this_event()
    {
        $this->expectException(AccessDeniedException::class);

        $otherEvent = $this->prophesize(Event::class);
        $this->formTemplate->getEvent()->shouldBeCalled()->willReturn($otherEvent->reveal());

        ($this->updateParametersAction)(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->formTemplate->reveal()
        );
    }

    public function test_show_update_form_template()
    {
        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr', 'en']);

        $this->formTemplate->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->formTemplate->getTitle()->shouldBeCalled()->willReturn('My Logistic Form');
        $this->formTemplate->isPublished()->shouldBeCalled()->willReturn(true);
        $this->formTemplate->getLocalizedTitle('fr')->shouldBeCalled()->willReturn('Logistique');
        $this->formTemplate->getLocalizedTitle('en')->shouldBeCalled()->willReturn('Logistic');

        $updateParameters = new UpdateParameters($this->formTemplate->reveal());

        $formView = $this->prophesize(FormView::class);
        $form = $this->prophesize(FormInterface::class);
        $form->handleRequest($this->request->reveal())
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;
        $form->isSubmitted()
            ->shouldBeCalled()
            ->willReturn(false)
        ;
        $form->createView()
            ->shouldBeCalled()
            ->willReturn($formView->reveal())
        ;
        $this->formFactory
            ->create(UpdateParametersType::class, $updateParameters, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->twig
            ->render(
                'AdminBundle:Template/Form:updateParameters.html.twig',
                [
                    'event' => $this->event->reveal(),
                    'form' => $formView->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn('Update parameters form')
        ;

        $response = ($this->updateParametersAction)(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->formTemplate->reveal()
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('Update parameters form', $response->getContent());
    }

    public function test_handle_update_form_template()
    {
        $this->event->getLocales()->shouldBeCalled()->willReturn(['fr', 'en']);
        $this->event->getId()->shouldBeCalled()->willReturn(1337);

        $this->formTemplate->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->formTemplate->getTitle()->shouldBeCalled()->willReturn('My Logistic Form');
        $this->formTemplate->isPublished()->shouldBeCalled()->willReturn(true);
        $this->formTemplate->getLocalizedTitle('fr')->shouldBeCalled()->willReturn('Logistique');
        $this->formTemplate->getLocalizedTitle('en')->shouldBeCalled()->willReturn('Logistic');

        $updateParameters = new UpdateParameters($this->formTemplate->reveal());

        $form = $this->prophesize(FormInterface::class);
        $form->handleRequest($this->request->reveal())
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;
        $form->isSubmitted()
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $form->isValid()
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this->formFactory
            ->create(UpdateParametersType::class, $updateParameters, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form->reveal())
        ;

        $this->commandBus
            ->handle($updateParameters)
            ->shouldBeCalled()
        ;

        $this->flashBag
            ->add('success', 'flash.admin.form.template.update_parameters.success')
            ->shouldBeCalled()
        ;

        $this->router
            ->generate('admin_template_form_list', ['event' => 1337])
            ->shouldBeCalled()
            ->willReturn('/event/1337/path/to/list')
        ;

        $response = ($this->updateParametersAction)(
            $this->request->reveal(),
            $this->event->reveal(),
            $this->formTemplate->reveal()
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/event/1337/path/to/list', $response->getTargetUrl());
    }
}
