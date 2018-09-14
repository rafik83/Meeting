<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route;

use Proximum\Vimeet\Application\Components\Home\HomeDispatch;
use Proximum\Vimeet\Application\Components\Home\HomeDispatchAnonymousUser;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Adapter\AuthorizationCheckerAdapter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\User\UserInterface;

class HomeUserDispatcher
{
    /** @var Router */
    private $router;

    /** @var HomeDispatch */
    private $homeDispatch;

    /** @var HomeDispatchAnonymousUser */
    private $homeDispatchAnonymousUser;

    /** @var AuthorizationCheckerAdapter */
    private $authorizationChecker;

    /** @var DDayGuesser */
    private $dayGuesser;

    /** @var AgendaAccessChecker */
    private $agendaAccessChecker;

    public function __construct(
        Router $router,
        HomeDispatch $homeDispatch,
        HomeDispatchAnonymousUser $homeDispatchAnonynmousUser,
        AuthorizationCheckerAdapter $authorizationChecker,
        DDayGuesser $dayGuesser,
        AgendaAccessChecker $agendaAccessChecker
    ) {
        $this->router = $router;
        $this->homeDispatch = $homeDispatch;
        $this->homeDispatchAnonymousUser = $homeDispatchAnonynmousUser;
        $this->authorizationChecker = $authorizationChecker;
        $this->dayGuesser = $dayGuesser;
        $this->agendaAccessChecker = $agendaAccessChecker;
    }

    /**
     * @param Event              $event
     * @param null|UserInterface $user
     *
     * @return RedirectResponse|null
     */
    public function attemptDispatchUser(Event $event, UserInterface $user = null): ?RedirectResponse
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') && $user instanceof User) {
            if (null !== $response = $this->attemptDispatchLoggedUser($event, $user)) {
                return $response;
            }
        }

        if (null !== $response = $this->attemptDispatchAnonymousUser($event)) {
            return $response;
        }

        return null;
    }

    /**
     * @param Event              $event
     * @param null|UserInterface $user
     *
     * @return null|RedirectResponse
     */
    private function attemptDispatchLoggedUser(Event $event, UserInterface $user = null): ?RedirectResponse
    {
        $homeDispatchView = $this->homeDispatch->handle($event, $user);

        if (null !== $homeDispatchView) {
            if ($homeDispatchView->isGroup()) {
                return new RedirectResponse($this->router->generate(
                    'event_sheet_group_index',
                    ['sheetGroup' => $homeDispatchView->getGroup()->getId()]
                ));
            }

            if ($homeDispatchView->isOneSheet()) {
                if ($this->dayGuesser->isItDDay($event) && $this->agendaAccessChecker->allowedToAccess($event)) {
                    return new RedirectResponse($this->router->generate(
                        'event_agenda',
                        ['sheet' => $homeDispatchView->getSheet()->getId()]
                    ));
                }

                return new RedirectResponse($this->router->generate(
                    'event_sheet_default',
                    ['sheet' => $homeDispatchView->getSheet()->getId()]
                ));
            }

            if ($homeDispatchView->isMultipleSheet()) {
                return new RedirectResponse($this->router->generate('event_select_sheet'));
            }
        }

        return null;
    }

    /**
     * @param Event $event
     *
     * @return null|RedirectResponse
     */
    private function attemptDispatchAnonymousUser(Event $event): ?RedirectResponse
    {
        $homeDispatchView = $this->homeDispatchAnonymousUser->handle($event);

        if (null !== $homeDispatchView) {
            if ($homeDispatchView->isRegistrationNotOpen() || $homeDispatchView->isRegistrationClosed()) {
                return new RedirectResponse($this->router->generate('event_waiting_page'));
            }
        }

        return null;
    }
}
