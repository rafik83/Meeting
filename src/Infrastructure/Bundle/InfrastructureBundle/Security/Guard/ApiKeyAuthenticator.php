<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Guard;

use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Exception\Event\EventException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class ApiKeyAuthenticator extends AbstractGuardAuthenticator
{
    /** @var EventByHostResolver */
    private $eventByHostResolver;

    public function __construct(
        EventByHostResolver $eventByHostResolver
    ) {
        $this->eventByHostResolver = $eventByHostResolver;
    }

    public function getCredentials(Request $request): array
    {
        $token = $request->headers->get('X-API-Key');

        try {
            $event = $this->eventByHostResolver->resolveEventFromHost($request->getHost());
        } catch (EventException $exception) {
            throw new BadRequestHttpException('Missing host for "event".');
        }

        return [
            'token' => $token,
            'event' => $event,
        ];
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $credentials['event']->getApiKey() === $credentials['token'];
    }

    public function supports(Request $request): bool
    {
        return $request->headers->has('X-API-Key');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        return null;
    }

    public function start(Request $request, ?AuthenticationException $authException = null) {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            return null;
        }

        return $userProvider->loadUserByUsername('api');
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
