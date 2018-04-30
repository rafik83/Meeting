<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Security\Guard;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Exception\Event\EventException;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Route\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /** @var EventByHostResolver */
    private $eventByHostResolver;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var \DateTimeInterface */
    private $now;

    public function __construct(
        EventByHostResolver $eventByHostResolver,
        UserRepositoryInterface $userRepository,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        \DateTimeInterface $now
    ) {
        $this->eventByHostResolver = $eventByHostResolver;
        $this->userRepository = $userRepository;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->now = $now;
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        $data = ['message' => 'Authentication Required'];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function getCredentials(Request $request): array
    {
        if (!$request->query->has('token')) {
            throw new BadRequestHttpException('Missing request parameter "token".');
        }

        $token = $request->query->get('token');
        $locale = $request->getLocale();

        try {
            $event = $this->eventByHostResolver->resolveEventFromHostAndLocale($request->getHost(), $locale);
        } catch (EventException $exception) {
            throw new BadRequestHttpException('Missing host for "event".');
        }

        return [
            'token' => $token,
            'event' => $event,
            'locale' => $locale,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $this->userRepository->findByAuthenticationTokenAndEvent(
            $credentials['token'],
            $credentials['event'],
            $this->now
        );
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        $this->flashBag->add('error', 'flash.authentication_token.invalid_link');

        return new RedirectResponse($this->router->generate('event_login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        dump($request, $token, $providerKey);die;
        //return tr
    }

    public function supports(Request $request): bool
    {
        return
            Route::USER_EVENT_AUTHENTICATION_TOKEN_LOGIN === $request->attributes->get('_route') &&
            'GET' === $request->getMethod() &&
            true === (bool) $request->query->has('token');
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
