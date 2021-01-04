<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Token;

use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgenda;
use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgendaHandler;
use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenUnexpectedTypeException;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEventTokenController extends Controller
{
    /**
     * @param Request        $request
     * @param EventDomain    $eventDomain
     * @param UserEventToken $userEventToken
     * @param UserInterface  $user
     *
     * @return Response
     */
    public function confirmAgendaAction(
        Request $request,
        EventDomain $eventDomain,
        UserEventToken $userEventToken,
        UserInterface $user = null
    ) {
        $event = $eventDomain->getEvent();

        if ($userEventToken->getEvent() !== $event || !$userEventToken->isAgendaConfirmation()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($user instanceof User && $user !== $userEventToken->getUser()) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        $user = $userEventToken->getUser();

        try {
            $confirmAgenda = new ConfirmAgenda($userEventToken);
            $result = $this->get('tactician.commandbus')->handle($confirmAgenda);
            $alreadyConfirmed = ConfirmAgendaHandler::ALREADY_CONFIRMED === $result;
        } catch (UserEventTokenUnexpectedTypeException $userEventTokenUnexpectedTypeException) {
            throw $this->createNotFoundException('Token type unexpected. Expected an agenda confirmation token');
        }

        $sendCodeView = $this->get('handler.user.phone.send_code_form_handler')->handle(
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
