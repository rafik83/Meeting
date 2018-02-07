<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Client\WSSoapClient;

class ComexposiumWebservice
{
    private const PLATFORM_PARAMETERS_WSDL = 'http://webservices.comexposium-admin.com/catalogue-ws-v2/parametrageplateformews.wsdl';
    private const PLATFORM_EVENT_PARAMETERS_WSDL = 'http://webservices.comexposium-admin.com/catalogue-ws-v2/parametragecataloguews.wsdl';
    private const PLATFORM_REGISTRATIONS_WSDL = 'http://webservices.comexposium-admin.com/catalogue-ws-v2/inscriptionclientws.wsdl';

    private const OPERATION_GET_PARAMETERS = 'getParametrages';
    private const OPERATION_GET_EVENTS = 'getManifestations';
    private const OPERATION_GET_REGISTRATIONS = 'getInscriptions';

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return mixed
     * @throws \SoapFault
     */
    public function getEvents()
    {
        $client = $this->getClient(self::PLATFORM_PARAMETERS_WSDL);

        return $client->call(self::OPERATION_GET_EVENTS, []);
    }

    /**
     * @return mixed
     * @throws \SoapFault
     */
    public function getParameters()
    {
        $client = $this->getClient(self::PLATFORM_PARAMETERS_WSDL);

        return $client->call(self::OPERATION_GET_PARAMETERS, []);
    }

    /**
     * @return mixed
     * @throws \SoapFault
     */
    public function getRegistrations()
    {
        $client = $this->getClient(self::PLATFORM_REGISTRATIONS_WSDL);

        return $client->call(self::OPERATION_GET_REGISTRATIONS, []);
    }

    /**
     * @param string $eventReference
     *
     * @return mixed
     * @throws \SoapFault
     */
    public function getRegistrationsReference(string $eventReference)
    {
        $client = $this->getClient(self::PLATFORM_REGISTRATIONS_WSDL);

        $response = $client->call(
            'getReferenceInscriptions',
            [
                'getReferenceInscriptionsRequest' => [
                    'referenceManifestation' => $eventReference,
                ],
            ]
        );

        return $response;
    }

    /**
     * @param string $eventReference
     *
     * @return mixed
     * @throws \SoapFault
     */
    public function getEventParameters(string $eventReference)
    {
        $client = $this->getClient(self::PLATFORM_EVENT_PARAMETERS_WSDL);

        return $client->call(
            'getParametragesCatalogue',
            [
                'getParametragesCatalogueRequest' => [
                    'referenceManifestation' => $eventReference,
                ],
            ]
        );
    }

    /**
     * @param string $eventReference
     * @param string $registrationReference
     *
     * @return mixed
     * @throws \SoapFault
     */
    public function getRegistration(string $eventReference, string $registrationReference)
    {
        $client = $this->getClient(self::PLATFORM_REGISTRATIONS_WSDL);

        return $client->call(
            'getInscriptions',
            [
                'getInscriptionsRequest' => [
                    'referenceManifestation' => $eventReference,
                    'referenceInscription' => $registrationReference,
                ],
            ]
        );
    }

    /**
     * @param string $wsdl
     *
     * @return WSSoapClient
     */
    private function getClient(string $wsdl): WSSoapClient
    {
        $client = new WSSoapClient($wsdl);
        $client->setUsernameToken($this->username, $this->password);

        return $client;
    }
}
