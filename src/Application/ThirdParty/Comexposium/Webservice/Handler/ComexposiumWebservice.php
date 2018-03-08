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

        return $client->call('getManifestations', []);
    }

    /**
     * @return mixed
     * @throws \SoapFault
     */
    public function getParameters()
    {
        $client = $this->getClient(self::PLATFORM_PARAMETERS_WSDL);

        return $client->call('getParametrages', []);
    }

    /**
     * @param string $eventReference
     *
     * @return string[] array of registration reference
     * @throws \DomainException
     * @throws \SoapFault
     */
    public function getRegistrationsReference(string $eventReference): array
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

        if (true === $response->avecErreur) {
            throw new \DomainException(
                sprintf(
                    'getReferenceInscriptions with "%s" $eventReference return errors with response : %s',
                    $eventReference,
                    json_encode($response)
                )
            );
        }

        return (array) ($response->referenceInscription ?? []);
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
     *
     * @return array
     * @throws \SoapFault
     */
    public function getEventNomenclatures(string $eventReference): array
    {
        $response = $this->getEventParameters($eventReference);

        return (array) $response->nomenclatureManifestation;
    }

    /**
     * @param string $eventReference
     * @param array  $registrationReferences
     *
     * @return array of \stdClass
     * @throws \DomainException
     * @throws \SoapFault
     */
    public function getRegistrations(string $eventReference, array $registrationReferences): array
    {
        $client = $this->getClient(self::PLATFORM_REGISTRATIONS_WSDL);

        $response = $client->call(
            'getInscriptions',
            [
                'getInscriptionsRequest' => [
                    'referenceManifestation' => $eventReference,
                    'referenceInscription' => $registrationReferences,
                ],
            ]
        );

        if (true === $response->avecErreur) {
            throw new \DomainException(
                sprintf(
                    'getInscriptions with "%s" eventReference and "%s" registrationReferences return errors with response : %s',
                    $eventReference,
                    $registrationReferences,
                    json_encode($response)
                )
            );
        }

        return \is_array($response->inscription) ? $response->inscription : [$response->inscription];
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
