<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\User\ForgottenPassword;
use Proximum\Vimeet\Application\Command\User\NewPassword;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\ForgottenPasswordType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\NewPasswordType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForgottenPasswordController extends AbstractController
{
    private AuthenticationManagerInterface $authenticationManager;
    private LoggerInterface $logger;
    private CommandBusInterface $commandBus;

    public function __construct(
        AuthenticationManagerInterface $authenticationManager,
        LoggerInterface $logger,
        CommandBusInterface $commandBus
    ) {
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
        $this->commandBus = $commandBus;
    }

    public function forgottenPasswordAction(Request $request, EventDomain $eventDomain): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        $forgottenPassword = new ForgottenPassword($eventDomain->getEvent(), $request->getLocale());
        $form = $this->createForm(ForgottenPasswordType::class, $forgottenPassword, [
            'action' => $this->generateUrl('event_forgotten_password'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($forgottenPassword);
            } catch (EmailDoesNotExistException $exception) {
                // log only: for security reasons, no message is displayed
                $this->logger->info(sprintf('Email %s is not registered', $form->get('email')->getData()));
            }

            return $this->redirectToRoute('event_forgotten_password_confirm');
        }

        return $this->render('EventBundle:ResetPassword:request_token.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form' => $form->createView(),
        ]);
    }

    public function forgottenPasswordConfirmAction(EventDomain $eventDomain): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        return $this->render('EventBundle:ResetPassword:confirm.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }

    public function createNewPasswordAction(
        Request $request,
        EventDomain $eventDomain,
        ForgottenPasswordToken $forgottenPasswordToken
    ): Response {
        if ($forgottenPasswordToken->isExpired(new \DateTime())) {
            throw $this->createNotFoundException('The token expired.');
        }

        $newPassword = new NewPassword($forgottenPasswordToken->getUser(), $eventDomain->getEvent());
        $form = $this->createForm(NewPasswordType::class, $newPassword, [
            'action' => $this->generateUrl('event_create_new_password', [
                'token' => $forgottenPasswordToken->getToken(),
            ]),
            'method' => 'POST',
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($newPassword);
            $this->authenticationManager->authenticate($forgottenPasswordToken->getUser(), 'main');
            $this->addFlash('success', 'flash.new_password.success');

            return $this->redirectToRoute('event');
        }

        return $this->render('EventBundle:ResetPassword:new_password.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form' => $form->createView(),
        ]);
    }
}
