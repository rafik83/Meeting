<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Token;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgenda;
use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgendaHandler;
use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenUnexpectedTypeException;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeFormHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEventTokenController extends AbstractController
{
    private AuthenticationManagerInterface $autenticationManager;
    private SendCodeFormHandler $sendCodeFormHandler;
    private CommandBusInterface $commandBus;

    public function __construct(
        AuthenticationManagerInterface $autenticationManager,
        SendCodeFormHandler $sendCodeFormHandler,
        CommandBusInterface $commandBus
    ) {
        $this->autenticationManager = $autenticationManager;
        $this->sendCodeFormHandler = $sendCodeFormHandler;
        $this->commandBus = $commandBus;
    }

    public function confirmAgendaAction(
        Request $request,
        EventDomain $eventDomain,
        UserEventToken $userEventToken,
        UserInterface $user = null
    ): Response {
        $event = $eventDomain->getEvent();

        if ($userEventToken->getEvent() !== $event || !$userEventToken->isAgendaConfirmation()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($user instanceof User && $user !== $userEventToken->getUser()) {
            $this->autenticationManager->disconnect();
        }

        $user = $userEventToken->getUser();

        try {
            $confirmAgenda = new ConfirmAgenda($userEventToken);
            $result = $this->commandBus->handle($confirmAgenda);
            $alreadyConfirmed = ConfirmAgendaHandler::ALREADY_CONFIRMED === $result;
        } catch (UserEventTokenUnexpectedTypeException $userEventTokenUnexpectedTypeException) {
            throw $this->createNotFoundException('Token type unexpected. Expected an agenda confirmation token');
        }

        $sendCodeView = $this->sendCodeFormHandler->handle(
            new SendCodeForm($request, $user, $event)
        );

        if ($sendCodeView->isSuccess()) {
            return $this->redirectToRoute(
                'event_user_event_phone_validate_code_with_token',
                ['token' => $userEventToken->getToken()]
            );
        }

        return $this->render('EventBundle:Token\UserEventToken:agenda_confirmation.html.twig', [
            'event' => $eventDomain->getEvent(),
            'already_confirmed' => $alreadyConfirmed,
            'tipTranslationViews' => $sendCodeView->tipTranslationViews,
            'sendCodeForm' => null === $sendCodeView->form ? null : $sendCodeView->form->createView(),
        ]);
    }
}
