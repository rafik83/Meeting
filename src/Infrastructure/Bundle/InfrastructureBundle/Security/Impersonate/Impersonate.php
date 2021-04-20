<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class Impersonate
{
    // token life duration (seconds)
    const TIME_TO_LIVE = 60;

    /** @var UserProviderInterface */
    private $adminProvider;

    /** @var UserProviderInterface */
    private $userProvider;

    /** @var string */
    private $salt;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        UserProviderInterface $adminProvider,
        UserProviderInterface $userProvider,
        string $salt,
        \DateTimeInterface $dateTime
    ) {
        $this->adminProvider = $adminProvider;
        $this->userProvider = $userProvider;
        $this->salt = $salt;
        $this->dateTime = $dateTime;
    }

    public function getAdmin(string $token): UserInterface
    {
        return $this->getUserByProvider('admin', $token);
    }

    public function getUser(string $token): UserInterface
    {
        return $this->getUserByProvider('user', $token);
    }

    public function getEncodedToken(Admin $admin, User $user): string
    {
        $expire = $this->dateTime->getTimestamp() + self::TIME_TO_LIVE;
        $tokenString = $this->getTokenCheck($admin->getEmail(), $user->getEmail(), $expire);

        return base64_encode(
            serialize(
                [
                    'from'  => $admin->getEmail(),
                    'to'    => $user->getEmail(),
                    'expire' => $expire,
                    'check' => $tokenString,
                ]
            )
        );
    }

    /**
     * @throws BadCredentialsException
     */
    private function getUserByProvider(string $provider, string $token): UserInterface
    {
        $decodedToken = $this->decodeToken($token);

        $this->checkToken($decodedToken);

        if ('user' === $provider) {
            return $this->userProvider->loadUserByUsername($decodedToken['to']);
        } elseif ('admin' === $provider) {
            return $this->adminProvider->loadUserByUsername($decodedToken['from']);
        }

        throw new BadCredentialsException('Invalid provider');
    }

    /**
     * @throws BadCredentialsException
     */
    private function checkToken(array $decodedToken): void
    {
        if (!isset($decodedToken['from']) || !isset($decodedToken['from']) || !isset($decodedToken['from'])) {
            throw new BadCredentialsException('token params invalid');
        }

        $tokenCheck = $this->getTokenCheck($decodedToken['from'], $decodedToken['to'], $decodedToken['expire']);

        if ($tokenCheck !== $decodedToken['check']) {
            throw new BadCredentialsException('Token check invalid');
        }

        if ($this->dateTime->getTimestamp() > $decodedToken['expire']) {
            throw new BadCredentialsException('Token has expired');
        }
    }

    /**
     * @throws BadCredentialsException
     */
    private function decodeToken(string $token): array
    {
        $decodedToken = unserialize(base64_decode($token));

        if (!$decodedToken) {
            throw new BadCredentialsException('Token invalid');
        }

        return $decodedToken;
    }

    private function getTokenCheck(string $adminEmail, string $userEmail, int $expire): string
    {
        return hash_hmac('sha512', $adminEmail . $userEmail . $expire, $this->salt);
    }
}
