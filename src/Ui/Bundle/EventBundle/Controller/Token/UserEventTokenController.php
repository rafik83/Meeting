<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Token;

use Proximum\Vimeet\Application\Command\Token\UserEventToken\ConfirmAgenda;
use Proximum\Vimeet\Application\Command\User\Phone\SendCode;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewByUserQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenAlreadyConfirmedException;
use Proximum\Vimeet\Domain\Exception\Token\UserEventToken\UserEventTokenUnexpectedTypeException;
use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\Phone\SendCodeType;
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
        $locale = $request->getLocale();

        if ($userEventToken->getEvent() !== $event || !$userEventToken->isAgendaConfirmation()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($user instanceof User && $user !== $userEventToken->getUser()) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        $user = $userEventToken->getUser();
        $sendCode = new SendCode($user, $event, $user->getPhone(), $locale);

        $sendCodeForm = $this->createForm(SendCodeType::class, $sendCode, [
            'country' => $event->getCountry(),
            'submit' => true,
        ]);

        $alreadyConfirmed = false;

        try {
            $confirmAgenda = new ConfirmAgenda($userEventToken);
            $this->get('tactician.commandbus')->handle($confirmAgenda);
        } catch (UserEventTokenAlreadyConfirmedException $userEventTokenAlreadyConfirmed) {
            $alreadyConfirmed = true;
        } catch (UserEventTokenUnexpectedTypeException $userEventTokenUnexpectedTypeException) {
            throw $this->createNotFoundException('Token type unexpected. Expected an agenda confirmation token');
        }

        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle(
            new TipTranslationViewByUserQuery(
                $event,
                $userEventToken->getUser(),
                TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                $locale
            )
        );



        if ($sendCodeForm->handleRequest($request)->isSubmitted() && $sendCodeForm->isValid()) {

        }

        return $this->render('EventBundle:Token\UserEventToken:agenda_confirmation.html.twig', [
            'event' => $eventDomain->getEvent(),
            'already_confirmed' => $alreadyConfirmed,
            'tipTranslationViews' => $tipTranslationViews,
            'sendCodeForm' => $sendCodeForm->createView(),
        ]);
    }
}
