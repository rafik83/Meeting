<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Admin\ForgottenPassword;
use Proximum\Vimeet\Application\Command\Admin\NewPassword;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\Admin\ForgottenPasswordToken;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\ForgottenPasswordType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Admin\NewPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForgottenPasswordController extends AbstractController
{
    private ErrorFactory $errorFactory;
    private AuthenticationManagerInterface $authenticationManager;
    private CommandBusInterface $commandBus;

    public function __construct(
        ErrorFactory $errorFactory,
        AuthenticationManagerInterface $authenticationManager,
        CommandBusInterface $commandBus
    ) {
        $this->errorFactory = $errorFactory;
        $this->authenticationManager = $authenticationManager;
        $this->commandBus = $commandBus;
    }

    public function forgottenPasswordAction(Request $request): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('admin_event_list');
        }

        $forgottenPassword = new ForgottenPassword($request->getLocale());
        $form = $this->createForm(ForgottenPasswordType::class, $forgottenPassword, [
            'action' => $this->generateUrl('admin_forgot_password'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($forgottenPassword);
                $this->addFlash('success', 'flash.admin.reset_password_token.success');

                return $this->redirectToRoute('admin_login');
            } catch (EmailDoesNotExistException $exception) {
                $form->get('email')->addError($this->errorFactory->create('validators.emailDoesNotExist', $request->getLocale()));
            }
        }

        return $this->render('AdminBundle:ResetPassword:request_token.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function createNewPasswordAction(Request $request, ForgottenPasswordToken $forgottenPasswordToken): Response
    {
        if ($forgottenPasswordToken->isExpired(new \DateTime())) {
            $this->addFlash('error', 'flash.admin.reset_password_token.expired');

            return $this->redirectToRoute('admin_forgot_password');
        }

        $newPassword = new NewPassword($forgottenPasswordToken->getAdmin());
        $form = $this->createForm(NewPasswordType::class, $newPassword, [
            'action' => $this->generateUrl('admin_create_new_password', [
                'token' => $forgottenPasswordToken->getToken(),
            ]),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($newPassword);
            $this->authenticationManager->authenticate($forgottenPasswordToken->getAdmin(), 'admin');
            $this->addFlash('success', 'flash.new_password.success');

            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('AdminBundle:ResetPassword:new_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
