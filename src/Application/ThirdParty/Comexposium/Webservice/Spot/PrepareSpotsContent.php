<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot;

class PrepareSpotsContent
{
    /** @var array */
    public $rawRegistrationDataIndexedBySheetId;

    /**
     * @param array $rawRegistrationDataIndexedBySheetId raw "inscription" data from Comexposium indexed by Sheet id
     *                                                   See http://webservices.comexposium-admin.com/catalogue-ws-v2/inscriptionclientws.wsdl
     */
    public function __construct(array $rawRegistrationDataIndexedBySheetId)
    {
        $this->rawRegistrationDataIndexedBySheetId = $rawRegistrationDataIndexedBySheetId;
    }
}
