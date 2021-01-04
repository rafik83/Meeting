<?php

namespace Proximum\Vimeet\Domain\Model\User;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\User;

class ForgottenPasswordToken
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
    private $token;

    /**
     * @var DateTimeInterface
     */
    private $expireDate;

    /**
     * @param User              $user
     * @param string            $token
     * @param DateTimeInterface $expireDate
     */
    public function __construct(User $user, $token, DateTimeInterface $expireDate)
    {
        $this->user       = $user;
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
    public function getToken()
    {
        return $this->token;
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
