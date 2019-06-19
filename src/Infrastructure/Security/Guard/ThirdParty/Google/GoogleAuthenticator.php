<?php

namespace Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\Google;

use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GoogleUser;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleAuthenticator extends SocialAuthenticator
{
    /** @var ClientRegistry */
    private $clientRegistry;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        ClientRegistry $clientRegistry,
        UserRepositoryInterface $userRepository,
        RouterInterface $router
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->userRepository = $userRepository;
        $this->router = $router;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    private function getGoogleClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('google_main');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()->fetchUserFromToken($credentials);
        $user = $this->userRepository->findByEmail($googleUser->getEmail());

        if (!$user instanceof User) {
            throw new AuthenticationException('Email not found');
        }

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /** @var User $user */
        $user = $token->getUser();

        return new RedirectResponse($this->router->generate('event', ['_locale' => $user->getLocale()]));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
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
