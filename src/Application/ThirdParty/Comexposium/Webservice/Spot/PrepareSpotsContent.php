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
    public $sheetIdByReference;

    /** @var array */
    public $rawRegistrations;

    /**
     * @param array $sheetIdByReference example: [1337 => 678], 1337 is Comexposium reference and 678 is Vimeet
     *     reference.
     * @param array $rawRegistrations raw "inscription" data from Comexposium ;
     *     See http://webservices.comexposium-admin.com/catalogue-ws-v2/inscriptionclientws.wsdl
     */
    public function __construct(array $sheetIdByReference, array $rawRegistrations)
    {
        $this->sheetIdByReference = $sheetIdByReference;
        $this->rawRegistrations = $rawRegistrations;
    }
}
