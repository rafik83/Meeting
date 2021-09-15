<?php

namespace Proximum\Vimeet\Domain\Model\User;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ActivateAccountToken
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
     * @var Sheet
     */
    private $sheet;

    /**
     * @var DateTimeInterface
     */
    private $expireDate;

    /**
     * @param User              $user
     * @param string            $token
     * @param Sheet             $sheet
     * @param DateTimeInterface $expireDate
     */
    public function __construct(User $user, $token, Sheet $sheet, DateTimeInterface $expireDate)
    {
        $this->user       = $user;
        $this->token      = $token;
        $this->sheet      = $sheet;
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
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
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
