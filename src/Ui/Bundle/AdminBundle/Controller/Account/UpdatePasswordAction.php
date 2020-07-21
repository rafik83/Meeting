<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Account;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Admin\ChangePassword;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\ChangePasswordType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Templating\EngineInterface;

class UpdatePasswordAction
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $engine;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        EngineInterface $engine,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag
    ) {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain): Response
    {
        $changePassword = new ChangePassword($adminDomain->getAdmin());

        $form = $this->formFactory->create(ChangePasswordType::class, $changePassword, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($changePassword);
            $this->flashBag->add('success', 'flash.admin.change_password.success');

            return new RedirectResponse($this->router->generate('admin_account'));
        }

        return new Response($this->engine->render('AdminBundle:Account:updatePassword.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
