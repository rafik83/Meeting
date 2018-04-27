<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Impersonate;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class Impersonate
{
    /**
     * @var UserProviderInterface
     */
    private $adminProvider;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var string
     */
    private $salt;

    /**
     * @param UserProviderInterface $adminProvider
     * @param UserProviderInterface $userProvider
     * @param string                $salt
     */
    public function __construct(
        UserProviderInterface $adminProvider,
        UserProviderInterface $userProvider,
        $salt
    ) {
        $this->adminProvider = $adminProvider;
        $this->userProvider  = $userProvider;
        $this->salt          = $salt;
    }

    /**
     * @param string $token
     *
     * @return UserInterface
     */
    public function getAdmin($token)
    {
        return $this->getUserByProvider('admin', $token);
    }

    /**
     * @param string $token
     *
     * @return UserInterface
     */
    public function getUser($token)
    {
        return $this->getUserByProvider('user', $token);
    }

    /**
     * @param Admin $admin
     * @param User  $user
     *
     * @return string
     */
    public function getEncodedToken(Admin $admin, User $user)
    {
        $tokenString = $this->getTokenCheck($admin->getEmail(), $user->getEmail());

        return base64_encode(
            serialize(
                [
                    'from'  => $admin->getEmail(),
                    'to'    => $user->getEmail(),
                    'check' => $tokenString,
                ]
            )
        );
    }

    /**
     * @param string $provider
     * @param string $token
     *
     * @throws \Exception
     *
     * @return UserInterface
     */
    private function getUserByProvider($provider, $token)
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
     * @param array $decodedToken
     *
     * @throws \Exception
     */
    private function checkToken(array $decodedToken)
    {
        if (!isset($decodedToken['from']) || !isset($decodedToken['from']) || !isset($decodedToken['from'])) {
            throw new BadCredentialsException('token params invalid');
        }

        $tokenCheck = $this->getTokenCheck($decodedToken['from'], $decodedToken['to']);

        if ($tokenCheck !== $decodedToken['check']) {
            throw new BadCredentialsException('Token check invalid');
        }
    }

    /**
     * @param string $token
     *
     * @throws \Exception
     *
     * @return array
     */
    private function decodeToken($token)
    {
        $decodedToken = unserialize(base64_decode($token));

        if (!$decodedToken) {
            throw new BadCredentialsException('Token invalid');
        }

        return $decodedToken;
    }

    /**
     * @param string $adminEmail
     * @param string $userEmail
     *
     * @return string
     */
    private function getTokenCheck($adminEmail, $userEmail)
    {
        return sha1($adminEmail . $this->salt . $userEmail);
    }
}
