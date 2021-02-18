<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\User\ActivateAccountPassword;
use Proximum\Vimeet\Application\Command\User\ActivateAccount\ReSendActivateAccountToken;
use Proximum\Vimeet\Application\Components\Registration\StepManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\ActivateAccountPasswordType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ActivateAccountController extends AbstractController
{
    private AuthenticationManagerInterface $authenticationManager;
    private StepManager $registrationStepManager;
    private FlashBagInterface $flashBag;
    private CommandBusInterface $commandBus;

    public function __construct(
        AuthenticationManagerInterface $authenticationManager,
        StepManager $registrationStepManager,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus
    ) {
        $this->authenticationManager = $authenticationManager;
        $this->registrationStepManager = $registrationStepManager;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
    }

    public function passwordAction(
        Request $request,
        EventDomain $eventDomain,
        ActivateAccountToken $activateAccountToken
    ): Response {
        $sheet = $activateAccountToken->getSheet();
        $user  = $activateAccountToken->getUser();

        // We must refresh sheet to make behat feature working ...
        $this->getDoctrine()->getManager()->refresh($sheet);

        if (!$sheet->hasUser($user) || $activateAccountToken->getSheet()->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($activateAccountToken->isExpired(new \DateTime())) {
            return $this->redirectToRoute('event_activate_account_expired', [
                'token' => $activateAccountToken->getToken(),
            ]);
        }

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->authenticationManager->disconnect();
        }

        $command = new ActivateAccountPassword($user, $sheet);
        $form    = $this->createForm(ActivateAccountPasswordType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->authenticationManager->authenticate($command->user, 'main');

            $participant = $sheet->getUserParticipant($user);

            if (!$participant instanceof Participant) {
                return $this->redirectToRoute('event_sheet_default', [
                    'sheet' => $sheet->getId(),
                ]);
            }

            $registrationStepManager = $this->registrationStepManager;
            $redirectStep = $registrationStepManager->getRedirectStep($sheet, $participant);

            if (true === $redirectStep['redirect']) {
                return $this->redirectToRoute($redirectStep['route'], $redirectStep['parameters']);
            }

            return $this->redirectToRoute('event_account_participant', [
                'sheet'       => $sheet->getId(),
                'participant' => $participant->getId(),
            ]);
        }

        return $this->render('EventBundle:ActivateAccount:password.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
        ]);
    }

    public function expiredTokenAction(
        Request $request,
        EventDomain $eventDomain,
        ActivateAccountToken $activateAccountToken
    ): Response {
        $sheet = $activateAccountToken->getSheet();
        $user  = $activateAccountToken->getUser();

        // We must refresh sheet to make behat feature working ...
        $this->getDoctrine()->getManager()->refresh($sheet);

        if (!$sheet->hasUser($user) || $activateAccountToken->getSheet()->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->authenticationManager->disconnect();
        }

        $command = new ReSendActivateAccountToken($sheet, $user, $sheet->getOwner());

        if ($request->isMethod('POST')) {
            $this->commandBus->handle($command);
            $this->addFlash('reSendActivateAccountToken', 'confirm');

            return $this->redirectToRoute('event_actiavet_account_re_send_token_confirm');
        }

        return $this->render('EventBundle:ActivateAccount:expired.html.twig', [
            'event' => $eventDomain->getEvent(),
            'token' => $activateAccountToken->getToken(),
        ]);
    }

    public function confirmReSendTokenAction(Request $request, EventDomain $eventDomain): Response
    {
        if (empty($this->flashBag->get('reSendActivateAccountToken'))) {
            throw $this->createNotFoundException('Not allowed');
        }

        return $this->render('EventBundle:ActivateAccount:confirmReSendToken.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }

    public function completeProfileAction(Participant $participant): RedirectResponse
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->authenticationManager->disconnect();
        }

        $this->addFlash('login_email', $participant->getUser()->getEmail());

        return $this->redirectToRoute('event_account_participant', [
            'sheet'       => $participant->getSheet()->getId(),
            'participant' => $participant->getId(),
        ]);
    }
}
