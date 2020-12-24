<?php

namespace Proximum\Vimeet\Behat\Service\Manager\User\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\AuthenticationToken;
use Proximum\Vimeet\Domain\Repository\User\Event\AuthenticationTokenRepositoryInterface;

class AuthenticationTokenManager
{
    /** @var AuthenticationTokenRepositoryInterface */
    private $authenticationTokenRepository;

    public function __construct(AuthenticationTokenRepositoryInterface $authenticationTokenRepository)
    {
        $this->authenticationTokenRepository = $authenticationTokenRepository;
    }

    public function create(
        User $user,
        Event $event,
        string $token,
        ?\DateTimeInterface $expiredAt = null
    ): AuthenticationToken {
        $token = new AuthenticationToken($user, $event, $token, new \DateTime(), $expiredAt);

        $this->authenticationTokenRepository->add($token);

        return $token;
    }
}
