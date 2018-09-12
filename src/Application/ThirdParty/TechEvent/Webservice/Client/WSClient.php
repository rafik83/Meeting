<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Client;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Data\Type;

class WSClient
{
    /** @var HttpAdapterInterface */
    private $httpAdapter;

    public function __construct(HttpAdapterInterface $httpAdapter)
    {
        $this->httpAdapter = $httpAdapter;
    }

    public function getContactsToSynchro(string $endpoint, string $pIdAuth): array
    {
        try {

            $response = $this->httpAdapter->post($endpoint, [], ['pIdAuth' => $pIdAuth]);

            if ($response->statusCode === 200) {
                $xml = simplexml_load_string($response->body);

                if (false === $xml) {
                    return [];
                }

                $contacts = [];
                foreach ($xml->xpath('*/DocumentElement/Results') as $xmlContact) {
                    $json = json_encode($xmlContact);
                    $contact = json_decode($json, true);

                    $contacts[$contact[Type::ID_CONTACT]] = $contact;
                }

                return $contacts;
            }
        } catch (ServerErrorException $error) {
            return [];
        }
    }
}
