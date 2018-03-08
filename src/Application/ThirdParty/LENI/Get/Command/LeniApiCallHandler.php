<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command;

use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\MissingIdException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\WarningApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

class LeniApiCallHandler
{
    /** @var LeniApiCaller */
    private $leniApi;

    public function __construct(LeniApiCaller $leniApi)
    {
        $this->leniApi = $leniApi;
    }

    /**
     * @param LeniApiCall $command
     *
     * @throws LeniApiServerException
     * @throws \LogicException
     */
    public function handle(LeniApiCall $command)
    {
        $event = $pendingExtraData->getEvent();
        $data  = unserialize($pendingExtraData->getValue(), ['allowed_classes' => false]);

        // remove data userId when null
        if (array_key_exists(LeniConstants::LENI_COL_USER_ID, $data)
            && null === $data[LeniConstants::LENI_COL_USER_ID]
        ) {
            unset($data[LeniConstants::LENI_COL_USER_ID]);
        }

        var_dump($this->leniApi->get($event, $data));
    }
}
