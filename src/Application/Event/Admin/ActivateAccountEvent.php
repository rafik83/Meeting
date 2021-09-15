<?php

namespace Proximum\Vimeet\Application\Event\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Symfony\Component\EventDispatcher\Event;

class ActivateAccountEvent extends Event
{
    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var ActivateAccountToken
     */
    private $activateAccountToken;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param Admin                $admin
     * @param ActivateAccountToken $activateAccountToken
     * @param string               $locale
     */
    public function __construct(Admin $admin, ActivateAccountToken $activateAccountToken, $locale)
    {
        $this->admin                = $admin;
        $this->activateAccountToken = $activateAccountToken;
        $this->locale               = $locale;
    }

    /**
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return ActivateAccountToken
     */
    public function getActivateAccountToken()
    {
        return $this->activateAccountToken;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
