<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Guard\User\Event;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\User\Event\ActivateAccountTokenByUserAndSheetGuesser;
use Proximum\Vimeet\Application\Command\User\Event\ActivateAccountTokenByUserAndSheetGuesserHandler;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Exception\Event\EventException;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
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
    private $dateTime;

    public function __construct(
        EventByHostResolver $eventByHostResolver,
        UserRepositoryInterface $userRepository,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        \DateTimeInterface $dateTime
    ) {
        $this->eventByHostResolver = $eventByHostResolver;
        $this->userRepository = $userRepository;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->dateTime = $dateTime;
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

    public function getUser($credentials, UserProviderInterface $userProvider): ?User
    {
        return $this->userRepository->findByAuthenticationTokenAndEvent(
            $credentials['token'],
            $credentials['event'],
            $this->dateTime
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

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
        /** @var User $user */
        $user = $token->getUser();

        return new RedirectResponse($this->router->generate('event', ['_locale' => $user->getLocale()]));
    }

    public function supports(Request $request): bool
    {
        return
            \in_array($request->attributes->get('_route'),
                [
                    Route::LOGIN,
                    Route::USER_EVENT_AUTHENTICATION_TOKEN_LOGIN,
                ],
                true
            )
            && 'GET' === $request->getMethod()
            && true === (bool)$request->query->has('token');
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
