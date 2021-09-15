<?php

namespace Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\Comexposium;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLogin;
use Proximum\Vimeet\Application\Command\Sheet\LastLogin\UpdateLastLoginHandler;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOChecker;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSOCheckerHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Query\SSORedirectionAfterLoginResolver;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Exception\SSOException;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Exception\Event\EventException;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class SSOAuthenticator extends AbstractGuardAuthenticator
{
    /** @var SSOCheckerHandler */
    private $SSOCheckerHandler;

    /** @var EventByHostResolver */
    private $eventByHostResolver;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var SSOAuthenticationSuccessHandler */
    private $SSOAuthenticationSuccessHandler;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var SSORedirectionAfterLoginResolver */
    private $SSORedirectionAfterLoginResolver;

    /** @var UpdateLastLoginHandler */
    private $updateLastLoginHandler;

    public function __construct(
        SSOCheckerHandler $SSOCheckerHandler,
        EventByHostResolver $eventByHostResolver,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        SSOAuthenticationSuccessHandler $SSOAuthenticationSuccessHandler,
        SSORedirectionAfterLoginResolver $SSORedirectionAfterLoginResolver,
        UpdateLastLoginHandler $updateLastLoginHandler
    ) {
        $this->SSOCheckerHandler = $SSOCheckerHandler;
        $this->eventByHostResolver = $eventByHostResolver;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->SSOAuthenticationSuccessHandler = $SSOAuthenticationSuccessHandler;
        $this->SSORedirectionAfterLoginResolver = $SSORedirectionAfterLoginResolver;
        $this->updateLastLoginHandler = $updateLastLoginHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return Route::EVENT_LOGIN_CHECK === $request->attributes->get('_route')
            && 'GET' === $request->getMethod()
            && true === (bool) $request->query->get('comexposium_sso')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws BadRequestHttpException
     * @throws BadCredentialsException
     */
    public function getCredentials(Request $request)
    {
        if (!$request->query->has('email')) {
            throw new BadRequestHttpException('Missing request parameter "email".');
        }

        if (!$request->query->has('token')) {
            throw new BadRequestHttpException('Missing request parameter "token".');
        }

        if (!$request->query->has('isExhibitor')) {
            throw new BadRequestHttpException('Missing request parameter "isExhibitor".');
        }

        $email = $request->query->get('email');
        $token = $request->query->get('token');
        $isExhibitor = (bool) $request->query->get('isExhibitor');
        $locale = $request->getLocale();

        try {
            $event = $this->eventByHostResolver->resolveEventFromHostAndLocale($request->getHost(), $locale);
        } catch (EventException $exception) {
            throw new BadRequestHttpException('Missing host for "event".');
        }

        if (null === $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_COMEXPOSIUM_SSO_ENABLED)) {
            throw new BadRequestHttpException('The sso for Comexposium is not enabled on this event');
        }

        return [
            'email' => $email,
            'token' => $token,
            'event' => $event,
            'isExhibitor' => $isExhibitor,
            'locale' => $locale,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws AuthenticationException
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            return $this->SSOCheckerHandler->handle(
                new SSOChecker(
                    $credentials['event'],
                    $credentials['email'],
                    $credentials['token'],
                    $credentials['isExhibitor'],
                    $credentials['locale']
                )
            );
        } catch (SSOException $exception) {
            throw new AuthenticationException('SSO not possible', $exception->getCode(), $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = ['message' => 'Authentication Required'];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->flashBag->add('error', 'flash.sso.comexposium.error');

        return new RedirectResponse($this->router->generate('event_login'));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     * @throws BadRequestHttpException
     */
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

        $generatedUrl = $this->SSORedirectionAfterLoginResolver->handle($event, $user);

        if (null !== $generatedUrl) {
            return new RedirectResponse($generatedUrl);
        }

        // if user is redirected (see previous lines) we consider that the user has no sheet
        // so, update sheet(s) last login only for not redirected user
        $this->updateLastLoginHandler->handle(new UpdateLastLogin($event, $user));

        return $this->SSOAuthenticationSuccessHandler->onAuthenticationSuccess($request, $token);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
