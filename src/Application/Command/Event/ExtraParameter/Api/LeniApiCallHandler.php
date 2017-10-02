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
            'Authorization' => 'Basic ' . base64_encode($command->leniUserParameter->getValue()),
            'Host' => self::LENI_HOST,
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($body),
            'Connection' => 'Close',
        ];

        try {
            $response = $this->httpAdapter->post(self::LENI_ENDPOINT, $headers, $body);
        } catch (ServerErrorException $exception) {

        }
    }
}
