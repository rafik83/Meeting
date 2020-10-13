<?php

namespace Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\TechEvent;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLogin;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLoginHandler;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Exception\Event\EventException;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
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

class TechEventTokenAuthenticator extends AbstractGuardAuthenticator
{
    /** @var EventByHostResolver */
    private $eventByHostResolver;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var UpdateLastLoginHandler */
    private $updateLastLoginHandler;

    public function __construct(
        EventByHostResolver $eventByHostResolver,
        ExtraDataRepositoryInterface $extraDataRepository,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        UpdateLastLoginHandler $updateLastLoginHandler
    ) {
        $this->eventByHostResolver = $eventByHostResolver;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->extraDataRepository = $extraDataRepository;
        $this->updateLastLoginHandler = $updateLastLoginHandler;
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        $data = ['message' => 'Authentication Required'];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function getCredentials(Request $request): array
    {
        if (!$request->query->has('techtoken')) {
            throw new BadRequestHttpException('Missing request parameter "techtoken".');
        }

        $token = $request->query->get('techtoken');

        try {
            $event = $this->eventByHostResolver->resolveEventFromHost($request->getHost());
        } catch (EventException $exception) {
            throw new BadRequestHttpException('Missing host for "event".');
        }

        return [
            'techtoken' => $token,
            'event' => $event,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?User
    {
        $extraData = $this->extraDataRepository->getExtraDataForEventNameAndMD5Value(
            $credentials['event'],
            Type::IMPORTED_FROM_TECH_EVENT,
            $credentials['techtoken']
        );

        if ($extraData === null) {
            throw new AuthenticationException('Token value not found.');
        }

        return $extraData->getUser();
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
        try {
            $event = $this->eventByHostResolver->resolveEventFromHostAndLocale(
                $request->getHost(),
                $request->getLocale()
            );
        } catch (EventException $exception) {
            throw new BadRequestHttpException('Missing host for "event".');
        }

        $user = $token->getUser();

        $this->updateLastLoginHandler->handle(new UpdateLastLogin($event, $user));

        return new RedirectResponse(
            $this->router->generate('event', ['_locale' => $user->getLocale()])
        );
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
            && true === (bool) $request->query->has('techtoken');
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
