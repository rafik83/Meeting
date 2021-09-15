<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\Account;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Admin\ChangePassword;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Account\UpdatePasswordAction;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\ChangePasswordType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Twig\Environment;

class UpdatePasswordActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $formFactory,
        $router,
        $twig,
        $commandBus,
        $flashBag,
        $admin,
        $request
    ;

    public function setUp(): void
    {
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->twig = $this->prophesize(Environment::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->request = $this->prophesize(Request::class);
    }

    public function testInvoke(): void
    {
        $form = $this->prophesize(Form::class);
        $view = $this->prophesize(FormView::class);
        $form->createView()->shouldBeCalled()->willReturn($view);
        $changePassword = new ChangePassword($this->admin->reveal());
        $this->formFactory->create(ChangePasswordType::class, $changePassword, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(false);

        $this->twig->render('AdminBundle:Account:updatePassword.html.twig', ['form' => $view])->shouldBeCalled()->willReturn('<html></html>');

        $action = new UpdatePasswordAction(
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($this->request->reveal(), new AdminDomain($this->admin->reveal()));

        $this->assertEquals('<html></html>', $result->getContent());
    }

    public function testHandle(): void
    {
        $form = $this->prophesize(Form::class);
        $changePassword = new ChangePassword($this->admin->reveal());
        $this->formFactory->create(ChangePasswordType::class, $changePassword, ['submit' => true])
            ->shouldBeCalled()
            ->willReturn($form)
        ;

        $form->handleRequest($this->request)->shouldBeCalled()->willReturn($form);
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $this->commandBus->handle($changePassword)->shouldBeCalled();
        $this->flashBag->add('success', 'flash.admin.change_password.success')->shouldBeCalled();
        $this->router->generate('admin_account')->shouldBeCalled()->willReturn('/account');

        $this->twig->render('AdminBundle:Account:updatePassword.html.twig', Argument::any())->shouldNotBeCalled();

        $action = new UpdatePasswordAction(
            $this->formFactory->reveal(),
            $this->router->reveal(),
            $this->twig->reveal(),
            $this->commandBus->reveal(),
            $this->flashBag->reveal()
        );

        $result = $action($this->request->reveal(), new AdminDomain($this->admin->reveal()));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals('/account', $result->getTargetUrl());
    }
}
