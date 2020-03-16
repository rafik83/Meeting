<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class AuthenticationFailureSubscriber implements EventSubscriberInterface
{
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

        dump($email);
        exit;
    }
}
