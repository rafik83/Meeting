<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\Comexposium\SSO;

use Proximum\Vimeet\Application\Adapter\AuthenticationManagerInterface;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOChecker;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\SSOException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class LoginAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var AuthenticationManagerInterface */
    private $authenticationManager;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param AuthenticationManagerInterface       $authenticationManager
     * @param QueryBusInterface                    $queryBus
     * @param FlashBagInterface                    $flashBag
     * @param RouterInterface                      $router
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        AuthenticationManagerInterface $authenticationManager,
        QueryBusInterface $queryBus,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse
     */
    public function __invoke(Request $request, EventDomain $eventDomain): RedirectResponse
    {
        if ($this->authorizationCheckerAdapter->isGranted('AUTHENTICATED_REMEMBERED')) {
            $this->authenticationManager->disconnect();
        }

        $email = $request->request->get('email', '');
        $token = $request->request->get('token', '');

        try {
            $user = $this->queryBus->handle(new SSOChecker($eventDomain->getEvent(), $email, $token));

            $this->authenticationManager->authenticate($user, 'main');
        } catch (SSOException $exception) {
            $this->flashBag->add('error', 'flash.sso.comexposium.error');
        }

        return new RedirectResponse($this->router->generate('event'));
    }
}
