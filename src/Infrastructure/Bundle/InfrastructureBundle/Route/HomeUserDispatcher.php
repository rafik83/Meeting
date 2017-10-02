<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route;

use Proximum\Vimeet\Application\Components\Home\HomeDispatch;
use Proximum\Vimeet\Application\Components\Home\HomeDispatchAnonymousUser;
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

    /**
     * HomeUserDispatcher constructor.
     *
     * @param Router $router
     * @param HomeDispatch $homeDispatch
     * @param HomeDispatchAnonymousUser $homeDispatchAnonynmousUser
     * @param AuthorizationCheckerAdapter $authorizationChecker
     */
    public function __construct(
        Router $router,
        HomeDispatch $homeDispatch,
        HomeDispatchAnonymousUser $homeDispatchAnonynmousUser,
        AuthorizationCheckerAdapter $authorizationChecker
    ) {
        $this->router = $router;
        $this->homeDispatch = $homeDispatch;
        $this->homeDispatchAnonymousUser = $homeDispatchAnonynmousUser;
        $this->authorizationChecker = $authorizationChecker;
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

        if ($homeDispatchView !== null) {
            if ($homeDispatchView->isGroup()) {
                return new RedirectResponse($this->router->generate(
                    'event_sheet_group_index',
                    ['sheetGroup' => $homeDispatchView->getGroup()->getId()]
                ));
            }

            if ($homeDispatchView->isOneSheet()) {
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

        if ($homeDispatchView !== null) {
            if ($homeDispatchView->isRegistrationNotOpen() || $homeDispatchView->isRegistrationClosed()) {
                return new RedirectResponse($this->router->generate('event_waiting_page'));
            }
        }

        return null;
    }
}
