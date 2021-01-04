<?php

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

class ChangeMailToken
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $mail;

    /**
     * @var string
     */
    private $token;

    /**
     * @var DateTimeInterface
     */
    private $expireDate;

    /**
     * @param User              $user
     * @param string            $mail
     * @param string            $token
     * @param DateTimeInterface $expireDate
     */
    public function __construct(User $user, $mail, $token, DateTimeInterface $expireDate)
    {
        $this->user       = $user;
        $this->mail       = $mail;
        $this->token      = $token;
        $this->expireDate = $expireDate;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return DateTimeInterface
     */
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    /**
     * @param DateTimeInterface $dateTime
     *
     * @return bool
     */
    public function isExpired(\DateTimeInterface $dateTime)
    {
        return $dateTime > $this->expireDate;
    }
}
