<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Http\Firewall;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate\Impersonate;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * SwitchUserListener allows a user to impersonate another one temporarily
 * This class is an override of the default Symfony SwitchUserListener class.
 */
class SwitchUserListener implements ListenerInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var string
     */
    private $providerKey;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    /**
     * @var string
     */
    private $switchUserParameter;

    /**
     * @var string
     */
    private $role;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var EventDispatcherInterface|null
     */
    private $dispatcher;

    /**
     * @var Impersonate
     */
    private $impersonate;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @param TokenStorageInterface          $tokenStorage
     * @param UserProviderInterface          $provider
     * @param UserCheckerInterface           $userChecker
     * @param string                         $providerKey
     * @param AccessDecisionManagerInterface $accessDecisionManager
     * @param LoggerInterface|null           $logger
     * @param string                         $switchUserParameter
     * @param string                         $role
     * @param EventDispatcherInterface|null  $dispatcher
     * @param bool                           $stateless
     * @param Impersonate                    $impersonate
     * @param EventRepositoryInterface       $eventRepository
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserProviderInterface $provider,
        UserCheckerInterface $userChecker,
        $providerKey,
        AccessDecisionManagerInterface $accessDecisionManager,
        LoggerInterface $logger = null,
        $switchUserParameter = '_switchto',
        $role = 'ROLE_ALLOWED_TO_SWITCH',
        EventDispatcherInterface $dispatcher = null,
        bool $stateless = false,
        Impersonate $impersonate,
        EventRepositoryInterface $eventRepository
    ) {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->tokenStorage          = $tokenStorage;
        $this->userChecker           = $userChecker;
        $this->providerKey           = $providerKey;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->switchUserParameter   = $switchUserParameter;
        $this->role                  = $role;
        $this->logger                = $logger;
        $this->dispatcher            = $dispatcher;
        $this->impersonate           = $impersonate;
        $this->eventRepository       = $eventRepository;
    }

    /**
     * Handles the switch to another user.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     *
     * @throws AccessDeniedException
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->get($this->switchUserParameter)) {
            return;
        }

        if ('_exit' === $request->get($this->switchUserParameter)) {
            if (null === $request->get('_redirect')) {
                throw new AccessDeniedException('Missing _redirect url parameter');
            }

            try {
                $this->attemptExitUser();
                $response = new RedirectResponse($request->get('_redirect'), 302);
                $event->setResponse($response);

                return;
            } catch (\Exception $exception) {
                throw new AccessDeniedException(
                    sprintf('Impossible to exit impersonation : "%s"', $exception->getMessage())
                );
            }
        }

        try {
            $token = $this->attemptSwitchUser($request);
            $this->tokenStorage->setToken($token);
        } catch (\Exception $exception) {
            throw new AccessDeniedException(sprintf('Switch User failed: "%s"', $exception->getMessage()));
        }

        $request->query->remove($this->switchUserParameter);
        $request->server->set('QUERY_STRING', http_build_query($request->query->all()));

        $response = new RedirectResponse($request->getUri(), 302);

        $event->setResponse($response);
    }

    /**
     * Attempts to switch to another user.
     *
     * @param Request $request A Request instance
     *
     * @throws \LogicException
     * @throws AccessDeniedException
     *
     * @return TokenInterface|null The new TokenInterface if successfully switched, null otherwise
     */
    private function attemptSwitchUser(Request $request)
    {
        $token = $request->get($this->switchUserParameter);

        if (null !== $this->logger) {
            $this->logger->info('Attempting to switch to user.', ['token' => $token]);
        }

        $admin      = $this->impersonate->getAdmin($token);
        $adminToken = new UsernamePasswordToken($admin, null, $this->providerKey, $admin->getRoles());

        if (false === $this->accessDecisionManager->decide($adminToken, [$this->role])) {
            throw new AccessDeniedException();
        }

        $event = $this->eventRepository->getEventByDomain($request->getHost());

        if (false === $this->accessDecisionManager->decide($adminToken, ['PERMISSION_EVENT_ACCESS'], $event)) {
            throw new AccessDeniedException('This admin can not access to this event');
        }

        $user = $this->impersonate->getUser($token);
        $this->userChecker->checkPostAuth($user);

        $roles   = $user->getRoles();
        $roles[] = new SwitchUserRole('ROLE_PREVIOUS_ADMIN', $adminToken);

        $token = new UsernamePasswordToken($user, $user->getPassword(), $this->providerKey, $roles);

        if (null !== $this->dispatcher) {
            $switchEvent = new SwitchUserEvent($request, $token->getUser());
            $this->dispatcher->dispatch(SecurityEvents::SWITCH_USER, $switchEvent);
        }

        return $token;
    }

    /**
     * Attempts to exit from an already switched user.
     *
     * @throws AuthenticationCredentialsNotFoundException
     */
    private function attemptExitUser()
    {
        $token = $this->tokenStorage->getToken();

        if (!$token || !$this->getOriginalToken($token)) {
            throw new AuthenticationCredentialsNotFoundException('Could not find original Token object.');
        }

        $this->tokenStorage->setToken(null);
    }

    /**
     * Gets the original Token from a switched one.
     *
     * @param TokenInterface $token A switched TokenInterface instance
     *
     * @return TokenInterface|false The original TokenInterface instance, false if the current TokenInterface is not switched
     */
    private function getOriginalToken(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                return $role->getSource();
            }
        }

        return false;
    }
}
