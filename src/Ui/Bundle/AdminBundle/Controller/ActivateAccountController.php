<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Admin\ActivateAccountPassword;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\ActivateAccountPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateAccountController extends AbstractController
{
    private AuthenticationManagerInterface $authenticationManager;
    private CommandBusInterface $commandBus;

    public function __construct(AuthenticationManagerInterface $authenticationManager, CommandBusInterface $commandBus)
    {
        $this->authenticationManager = $authenticationManager;
        $this->commandBus = $commandBus;
    }

    public function passwordAction(Request $request, ActivateAccountToken $activateAccountToken):  Response
    {
        $admin = $activateAccountToken->getAdmin();

        if ($activateAccountToken->isExpired(new \DateTime())) {
            throw $this->createNotFoundException('The token is expired.');
        }

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->authenticationManager->disconnect();
        }

        $command = new ActivateAccountPassword($admin);
        $form = $this->createForm(ActivateAccountPasswordType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->authenticationManager->authenticate($command->admin, 'admin');
            $this->addFlash('success', 'flash.admin.activate_account.success');

            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('AdminBundle:ActivateAccount:password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
