<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter\Api;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\Exception\Api\Leni\LeniApiServerException;
use Proximum\Vimeet\Application\Exception\Api\Leni\NotValidApiCallException;
use Proximum\Vimeet\Application\Exception\Api\Leni\WarningApiCallException;
use Proximum\Vimeet\Application\Serializer\Normalizer\Api\Leni\LeniUserViewNormalizer;

class LeniApiCallHandler
{
    const LENI_ENDPOINT = 'https://gateway.svc.exhibis.net/proximum/domain/IndividuEvt/Description/';
    const LENI_HOST = 'gateway.svc.exhibis.net';

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /**
     * @param HttpAdapterInterface $httpAdapter
     */
    public function __construct(HttpAdapterInterface $httpAdapter)
    {
        $this->httpAdapter = $httpAdapter;
    }

    /**
     * @param LeniApiCall $command
     *
     * @throws LeniApiServerException
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     */
    public function handle(LeniApiCall $command)
    {
        $body = json_encode([
            "idEvt" => $command->leniUserEvent->getValue(),
            "idUser" => $command->leniUserView->id,
            "mode" => "MessageAndModifiedData",
            "app" => "B",
            "data" => $command->leniUserView->serializeContent,
        ]);

        $headers = [
            'Authorization'  => 'Basic ' . base64_encode($command->leniUserParameter->getValue()),
            'Host'           => self::LENI_HOST,
            'Content-Type'   => 'application/json',
            'Content-Length' => strlen($body),
            'Connection'     => 'Close',
        ];

        try {
            $jsonResponse = $this->httpAdapter->post(self::LENI_ENDPOINT, $headers, $body);

            $response = json_decode($jsonResponse->body, true);

            if (isset($response['IsValid']) && $response['IsValid'] === true) {
                if (isset($response['hasWarning']) && $response['hasWarning'] === true && isset($response['info'])) {
                    $warnings = [];

                    foreach ($response['info'] as $key => $info) {
                        if (in_array($key, LeniUserViewNormalizer::LENI_COLUMNS)) {
                            $warnings[] = $key;
                        }
                    }

                    if (!empty($warnings)) {
                        throw new WarningApiCallException($warnings);
                    }
                }
            } else {
                throw new NotValidApiCallException('Leni responded has a non valid response ' . $jsonResponse->body);
            }
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }
    }
}
