<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\LoginContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginContext extends RawMinkContext implements KernelAwareContext
{
    /** @var LoginContextProxyInterface */
    private $loginContextProxy;

    /** @var KernelInterface */
    private $kernel;

    /** @var string */
    private $baseUrl;

    /**
     * @param LoginContextProxyInterface $loginContextProxy
     */
    public function __construct(LoginContextProxyInterface $loginContextProxy)
    {
        $this->loginContextProxy = $loginContextProxy;
    }

    /**
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given I am logged with this user
     */
    public function iAmLoggedWithThisUser()
    {
        $user = $this->loginContextProxy->getStorage()->get('user');

        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Missing User');
        }

        $this->createLoginCookie($user, 'main');
    }

    /**
     * @Given I am logged with this admin
     */
    public function iAmLoggedWithThisAdmin()
    {
        $admin = $this->loginContextProxy->getStorage()->get('admin');

        if (!$admin instanceof Admin) {
            throw new \InvalidArgumentException('Missing Admin');
        }

        $this->createLoginCookie($admin, 'admin');
    }

    /**
     * @param UserInterface $user
     * @param string        $providerKey Firewall name (main, admin)
     *
     * @throws \Exception
     */
    private function createLoginCookie(UserInterface $user, $providerKey)
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof \Behat\Mink\Driver\BrowserKitDriver) {
            throw new \Exception('BrowserKitDriver not supported');
        }

        $client = $driver->getClient();
        $client->getCookieJar()->set(new Cookie(session_name(), true));

        $session = $client->getContainer()->get('session');

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_' . $providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
