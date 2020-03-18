<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Model\User\Account;
use Proximum\Vimeet\Domain\Time\DaysHelper;

/**
 * "Compte utilisateur".
 */
class User extends AbstractUser implements MailRecipientInterface
{
    private const FAILED_AUTHENTICATION_MAX = 5;

    /**
     * @var Account
     */
    private $account;

    /**
     * @var bool
     */
    private $welcomed = false;

    /** @var int */
    private $failedAuthentication = 0;

    /** @var null|\DateTimeInterface */
    private $lastFailedAuthentication;

    /**
     * @param string $email
     * @param string $salt
     * @param string $password
     * @param string $locale
     */
    public function __construct($email, $salt, $password, $locale)
    {
        parent::__construct($email, $salt, $password, $locale);

        $this->account = new Account();
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Account $account
     *
     * @return User
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return bool
     */
    public function isWelcomed()
    {
        return true === $this->welcomed;
    }

    /**
     * @return User
     */
    public function welcome()
    {
        $this->welcomed = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFullname()
    {
        if (null === $this->account) {
            return $this->email;
        }

        return $this->account->getFirstName() . ' ' . $this->account->getLastname();
    }

    /**
     * @return null|string
     */
    public function getFirstName()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getFirstName();
    }

    /**
     * @return null|string
     */
    public function getLastName()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getLastName();
    }

    /**
     * @return null|string
     */
    public function getAvatar()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getAvatar();
    }

    /**
     * @return null|string
     */
    public function getPosition()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getPosition();
    }

    /**
     * @return null|string
     */
    public function getPhone()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getPhone();
    }

    /**
     * @return null|string
     */
    public function getMobile()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getMobile();
    }

    /**
     * @return null|string
     */
    public function getAddress()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getAddress();
    }

    /**
     * @return null|string
     */
    public function getZipCode()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getZipCode();
    }

    /**
     * @return null|string
     */
    public function getCity()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getCity();
    }

    /**
     * @return null|string
     */
    public function getCountry()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getCountry();
    }

    /**
     * @return null|string
     */
    public function getWebsite()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getWebsite();
    }

    /**
     * @return null|string
     */
    public function getGender()
    {
        if (null === $this->account) {
            return '';
        }

        return $this->account->getGender();
    }

    public function updateLastFailedAuthentication(\DateTimeInterface $now): void
    {
        if ($this->isLastFailedAuthenticationExpired($now)) {
            $this->failedAuthentication = 0;
        }

        $this->lastFailedAuthentication = $now;
        ++$this->failedAuthentication;
    }

    public function isLastFailedAuthenticationExpired(\DateTimeInterface $now): bool
    {
        if (null === $this->lastFailedAuthentication) {
            return true;
        }

        $lastFailedAuthenticationPLus15Minutes = DaysHelper::cloneDateTime($this->lastFailedAuthentication);
        $lastFailedAuthenticationPLus15Minutes->add(new \DateInterval('PT15M'));

        return $lastFailedAuthenticationPLus15Minutes < $now;
    }

    public function isTemporaryDisabledDueToFailedAuthentication(\DateTimeInterface $now): bool
    {
        if (self::FAILED_AUTHENTICATION_MAX > $this->failedAuthentication) {
            return false;
        }

        return !$this->isLastFailedAuthenticationExpired($now);
    }

    public function getRemainingAuthenticationAttempt(\DateTimeInterface $now): int
    {
        return self::FAILED_AUTHENTICATION_MAX - $this->failedAuthentication;
    }
}
