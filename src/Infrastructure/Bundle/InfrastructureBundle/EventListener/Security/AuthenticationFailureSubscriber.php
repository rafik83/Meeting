<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security;

use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class AuthenticationFailureSubscriber implements EventSubscriberInterface
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(UserRepositoryInterface $userRepository, \DateTimeInterface $dateTime)
    {
        $this->userRepository = $userRepository;
        $this->dateTime = $dateTime;
    }

    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'processException',
        ];
    }

    public function processException(AuthenticationFailureEvent $authenticationFailureEvent): void
    {
        $token = $authenticationFailureEvent->getAuthenticationToken();

        if (!$token instanceof UsernamePasswordToken || 'main' !== $token->getProviderKey()) {
            return;
        }

        $email = $token->getUser();
        $user = $this->userRepository->findByEmail($email);

        if (null === $user) {
            return;
        }

        $user->updateLastFailedAuthentication($this->dateTime);
        $this->userRepository->set($user);
    }
}
