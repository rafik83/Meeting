<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query;

use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;

/**
 * Get custom Data to send to Save LENI Api but not to save in the fingerprint
 */
class GetCustomDataHandler
{
    public function handle(GetCustomData $getCustomData): array
    {
        $data = $getCustomData->data;

        // If User is new (No LENI id), set "API" to "EvenementOrigine" field
        if (!isset($data[LeniConstants::LENI_COL_USER_ID])) {
            $data[LeniConstants::LENI_COL_EVENT_ORIGIN] = LeniConstants::NEW_USER_EVENT_ORIGIN;
        }

        return $data;
    }
}
