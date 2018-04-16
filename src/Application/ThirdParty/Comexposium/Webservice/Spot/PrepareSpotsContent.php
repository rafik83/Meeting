<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot;

class PrepareSpotsContent
{
    /** @var array */
    public $rawRegistrationDataIndexedBySheetId;

    /**
     * @param array $rawRegistrationDataIndexedBySheetId raw "inscription" data from Comexposium indexed by Sheet id
     *     See http://webservices.comexposium-admin.com/catalogue-ws-v2/inscriptionclientws.wsdl
     */
    public function __construct(array $rawRegistrationDataIndexedBySheetId)
    {
        $this->rawRegistrationDataIndexedBySheetId = $rawRegistrationDataIndexedBySheetId;
    }
}
