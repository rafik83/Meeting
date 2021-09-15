<?php

namespace Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\Google;

use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\ThirdParty\Oauth2\GetOrCreateUser;
use Proximum\Vimeet\Application\ThirdParty\Oauth2\GetOrCreateUserHandler;
use Proximum\Vimeet\Domain\Event\EventByHostResolver;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleAuthenticator extends SocialAuthenticator
{
    /** @var ClientRegistry */
    private $clientRegistry;

    /** @var EventByHostResolver */
    private $eventByHostResolver;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var GetOrCreateUserHandler */
    private $getOrCreateUserHandler;

    public function __construct(
        ClientRegistry $clientRegistry,
        EventByHostResolver $eventByHostResolver,
        GetOrCreateUserHandler $getOrCreateUserHandler,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->eventByHostResolver = $eventByHostResolver;
        $this->getOrCreateUserHandler = $getOrCreateUserHandler;
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function getCredentials(Request $request)
    {
        $locale = $request->getLocale();
        $event = $this->eventByHostResolver->resolveEventFromHostAndLocale($request->getHost(), $locale);

        return [
            'accessToken' => $this->fetchAccessToken($this->getGoogleClient()),
            'event' => $event,
            'locale' => $locale
        ];
    }

    private function getGoogleClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('google_main');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
           /** @var GoogleUser $googleUser */
            $googleUser = $this->getGoogleClient()->fetchUserFromToken($credentials['accessToken']);

            return $this->getOrCreateUserHandler->handle(
                new GetOrCreateUser(
                    $credentials['event'],
                    $credentials['locale'],
                    $googleUser->getEmail(),
                    $googleUser->getFirstName(),
                    $googleUser->getLastName()
                )
            );
        } catch (\Exception $exception) {
            throw new AuthenticationException('Email not found');
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /** @var User $user */
        $user = $token->getUser();

        return new RedirectResponse($this->router->generate('event', ['_locale' => $user->getLocale()]));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->flashBag->add('error', 'flash.oauth2.login.error');

        return new RedirectResponse($this->router->generate('event_login'));
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            $this->router->generate('event_login'), // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
