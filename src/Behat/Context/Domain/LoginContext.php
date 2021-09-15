<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\MinkExtension\Context\RawMinkContext;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\LoginContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginContext extends RawMinkContext
{
    private KernelInterface $kernel;
    private LoginContextProxyInterface $loginContextProxy;

    /**
     * @param LoginContextProxyInterface $loginContextProxy
     */
    public function __construct(KernelInterface $kernel, LoginContextProxyInterface $loginContextProxy)
    {
        $this->kernel = $kernel;
        $this->loginContextProxy = $loginContextProxy;
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

        /** @var SessionInterface $session */
        $session = $client->getContainer()->get('session');

        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $session->set('_security_' . $providerKey, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->clear();
        $client->getCookieJar()->set($cookie);
    }
}
