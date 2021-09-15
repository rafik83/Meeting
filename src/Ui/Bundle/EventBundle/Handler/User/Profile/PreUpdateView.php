<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile;

class PreUpdateView
{
    /**
     * @var string|null
     */
    public $currentMobile;

    /**
     * @var string
     */
    public $preUpdateState;

    /**
     * PreUpdateView constructor.
     *
     * @param null|string $currentMobile
     * @param string      $preUpdateState
     */
    public function __construct($currentMobile, string $preUpdateState)
    {
        $this->currentMobile  = $currentMobile;
        $this->preUpdateState = $preUpdateState;
    }
}
