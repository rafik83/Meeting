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
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TechEventAuthenticator extends AbstractGuardAuthenticator
{
    /** @var EventByHostResolver */
    private $eventByHostResolver;

    /** @var ExtraDataRepositoryInterface */
    private $userEventExtraDataRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var TechEventAuthenticationSuccessHandler */
    private $techEventAuthenticationSuccessHandler;

    /** @var UpdateLastLoginHandler */
    private $updateLastLoginHandler;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    public function __construct(
        EventByHostResolver $eventByHostResolver,
        ExtraDataRepositoryInterface $userEventExtraDataRepository,
        UserRepositoryInterface $userRepository,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        UpdateLastLoginHandler $updateLastLoginHandler,
        TechEventAuthenticationSuccessHandler $techEventAuthenticationSuccessHandler,
        CsrfTokenManagerInterface $csrfTokenManager
    ) {
        $this->eventByHostResolver = $eventByHostResolver;
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->userRepository = $userRepository;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->techEventAuthenticationSuccessHandler = $techEventAuthenticationSuccessHandler;
        $this->updateLastLoginHandler = $updateLastLoginHandler;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return Route::TECH_EVENT_LOGIN_CHECK === $request->attributes->get('_route')
            && 'POST' === $request->getMethod()
        ;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = ['message' => 'Authentication Required'];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function getCredentials(Request $request)
    {
        $loginData = $request->request->get('login');
        $email = $loginData['username'] ?? null;
        $password = $loginData['password'] ?? null;
        $csrfToken = $loginData['_token'] ?? null;
        $locale = $request->getLocale();

        try {
            $event = $this->eventByHostResolver->resolveEventFromHostAndLocale($request->getHost(), $locale);
        } catch (EventException $exception) {
            throw new BadRequestHttpException('Missing host for "event".');
        }

        return [
            'email' => $email,
            'password' => $password,
            'event' => $event,
            'locale' => $locale,
            'csrf_token' => $csrfToken,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);

        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user instanceof User) {
            throw new AuthenticationException('The user does not exist');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $loginDataExtraData = $this->userEventExtraDataRepository->getExtraDataForEventNameAndUser(
            $credentials['event'],
            Type::TECH_EVENT_LOGIN_DATA,
            $user
        );

        if ($loginDataExtraData === null) {
            throw new BadCredentialsException('The user can not log in.');
        }

        $loginData = $loginDataExtraData->getValue();
        if (sha1($credentials['password']) !== $loginData) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->flashBag->add('error', 'Bad credentials.');

        return new RedirectResponse($this->router->generate('event_login_second_step'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
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

        return $this->techEventAuthenticationSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    public function supportsRememberMe()
    {
        return true;
    }
}
