<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command;

use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;

class LeniApiCall
{
    /** @var ExtraData */
    public $extraData;

    /**
     * @param ExtraData $extraData
     */
    public function __construct(ExtraData $extraData)
    {
        $this->extraData = $extraData;
    }
}
