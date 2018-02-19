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
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOChecker;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\SSOException;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LoginAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var AuthenticationManagerInterface */
    private $authenticationManager;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param AuthenticationManagerInterface       $authenticationManager
     * @param ExtraParameterRepositoryInterface    $extraParameterRepository
     * @param QueryBusInterface                    $queryBus
     * @param FlashBagInterface                    $flashBag
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        AuthenticationManagerInterface $authenticationManager,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        QueryBusInterface $queryBus,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->flashBag = $flashBag;
        $this->authenticationManager = $authenticationManager;
        $this->extraParameterRepository = $extraParameterRepository;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return JsonResponse
     *
     * @throws AccessDeniedException
     */
    public function __invoke(Request $request, EventDomain $eventDomain): JsonResponse
    {
        if (null === $this->extraParameterRepository->findByEventAndType($eventDomain->getEvent(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)) {
            throw new AccessDeniedException('The sso for Comexposium is not enabled on this event');
        }

        if ($this->authorizationCheckerAdapter->isGranted('AUTHENTICATED_REMEMBERED')) {
            $this->authenticationManager->disconnect();
        }

        $email = $request->request->get('email', '');
        $token = $request->request->get('token', '');

        try {
            $user = $this->queryBus->handle(new SSOChecker($eventDomain->getEvent(), $email, $token));

            $this->authenticationManager->authenticate($user, 'main');

            return new JsonResponse(['isLogged' => true]);
        } catch (SSOException $exception) {
            $this->flashBag->add('error', 'flash.sso.comexposium.error');

            return new JsonResponse(['isLogged' => false]);
        }
    }
}
