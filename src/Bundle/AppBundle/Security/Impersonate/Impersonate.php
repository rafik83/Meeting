<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Security\Impersonate;

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
     * @param string $adminEmail
     * @param string $userEmail
     *
     * @return string
     */
    public function getEncodedToken($adminEmail, $userEmail)
    {
        $tokenString = $this->getTokenCheck($adminEmail, $userEmail);

        return base64_encode(serialize(['from' => $adminEmail, 'to' => $userEmail, 'check' => $tokenString]));
    }

    /**
     * @param string $provider
     * @param string $token
     *
     * @return UserInterface
     * @throws \Exception
     */
    private function getUserByProvider($provider, $token)
    {
        $decodedToken = $this->decodeToken($token);

        $this->checkToken($decodedToken);

        if ($provider === 'user') {
            return $this->userProvider->loadUserByUsername($decodedToken['to']);
        } elseif ($provider === 'admin') {
            return $this->adminProvider->loadUserByUsername($decodedToken['from']);
        }

        throw new \Exception('invalid provider');
    }

    /**
     * @param array $decodedToken
     *
     * @throws \Exception
     */
    private function checkToken(array $decodedToken)
    {
        if (!isset($decodedToken['from']) || !isset($decodedToken['from']) || !isset($decodedToken['from'])) {
            throw new \Exception('token params invalid');
        }

        $tokenCheck = $this->getTokenCheck($decodedToken['from'], $decodedToken['to']);

        if ($tokenCheck !== $decodedToken['check']) {
            throw new \Exception('token check invalid');
        }
    }

    /**
     * @param string $token
     *
     * @return array
     * @throws \Exception
     */
    private function decodeToken($token)
    {
        $decodedToken = unserialize(base64_decode($token));

        if (!$decodedToken) {
            throw new \Exception('token invalid');
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
