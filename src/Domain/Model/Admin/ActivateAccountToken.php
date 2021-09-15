<?php

namespace Proximum\Vimeet\Domain\Model\Admin;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Admin;

class ActivateAccountToken
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var string
     */
    private $token;

    /**
     * @var DateTimeInterface
     */
    private $expireDate;

    /**
     * @param Admin             $admin
     * @param string            $token
     * @param DateTimeInterface $expireDate
     */
    public function __construct(Admin $admin, $token, DateTimeInterface $expireDate)
    {
        $this->admin      = $admin;
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
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
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
