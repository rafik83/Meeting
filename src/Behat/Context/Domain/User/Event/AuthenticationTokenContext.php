<?php

namespace Proximum\Vimeet\Behat\Context\Domain\User\Event;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\User\Event\AuthenticationTokenContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class AuthenticationTokenContext implements Context
{
    /** @var AuthenticationTokenContextProxyInterface */
    private $authenticationTokenContextProxy;

    public function __construct(AuthenticationTokenContextProxyInterface $authenticationTokenContextProxy)
    {
        $this->authenticationTokenContextProxy = $authenticationTokenContextProxy;
    }

    /**
     * @Given /^there is an authentication token "(?P<token>[^"]+)" for this user on this event$/
     */
    public function createAuthenticationTokenForGivenUserAndEvent(string $token): void
    {
        $event = $this->authenticationTokenContextProxy->getStorage()->get('event');
        $user = $this->authenticationTokenContextProxy->getStorage()->get('user');

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Missing User');
        }

        $authenticationToken = $this->authenticationTokenContextProxy
            ->getAuthenticationTokenManager()
            ->create($user, $event, $token);

        $this->authenticationTokenContextProxy
            ->getStorage()
            ->set('authenticationToken', $authenticationToken);
    }
}
