<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Token;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\Token\UserEventTokenContextProxyInterface;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class UserEventTokenContext implements Context
{
    /** @var UserEventTokenContextProxyInterface */
    private $userEventTokenContextProxy;

    /**
     * @param UserEventTokenContextProxyInterface $userEventTokenContextProxy
     */
    public function __construct(UserEventTokenContextProxyInterface $userEventTokenContextProxy)
    {
        $this->userEventTokenContextProxy = $userEventTokenContextProxy;
    }

    /**
     * @Given /^there is a confirmation agenda token "(?P<token>[^"]+)" for this user on this event$/
     *
     * @param string $token
     */
    public function createAgendaConfirmationUserEventToken($token)
    {
        $this->createUserEventToken(UserEventTokenType::AGENDA_CONFIRMATION, $token);
    }

    /**
     * @param string $type
     * @param string $token
     */
    private function createUserEventToken($type, $token)
    {
        $event = $this->userEventTokenContextProxy->getStorage()->get('event');
        $user  = $this->userEventTokenContextProxy->getStorage()->get('user');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        if (null === $user) {
            throw new \InvalidArgumentException('Missing User');
        }

        if (!in_array($type, UserEventTokenType::getUserEventTokenType())) {
            throw new \InvalidArgumentException('The given type is not a valid UserEventToken type');
        }

        $userEventToken = $this->userEventTokenContextProxy
            ->getUserEventTokenManager()
            ->create($event, $user, $type, $token)
        ;
        $this->userEventTokenContextProxy->getStorage()->set('userEventToken', $userEventToken);
    }
}
