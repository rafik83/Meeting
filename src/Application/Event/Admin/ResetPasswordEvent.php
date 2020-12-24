<?php

namespace Proximum\Vimeet\Application\Event\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ForgottenPasswordToken;
use Symfony\Component\EventDispatcher\Event;

class ResetPasswordEvent extends Event
{
    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var ForgottenPasswordToken
     */
    private $forgottenPasswordToken;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param Admin                  $admin
     * @param ForgottenPasswordToken $forgottenPasswordToken
     * @param string                 $locale
     */
    public function __construct(Admin $admin, ForgottenPasswordToken $forgottenPasswordToken, $locale)
    {
        $this->admin                  = $admin;
        $this->forgottenPasswordToken = $forgottenPasswordToken;
        $this->locale                 = $locale;
    }

    /**
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return ForgottenPasswordToken
     */
    public function getForgottenPasswordToken()
    {
        return $this->forgottenPasswordToken;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
