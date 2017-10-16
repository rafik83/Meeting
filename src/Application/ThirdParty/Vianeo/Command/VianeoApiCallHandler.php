<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Command;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Exception\VianeoApiServerException;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

/**
 * Vianeo Api Call Handler
 */
class VianeoApiCallHandler
{
    const DEFAULT_LOCALE = 'fr';
    const URL_TEMPLATE = '%endpoint%?option=%option%&task=%task%&sharedsecret=%sharedsecret%&jsonpayload=%jsonpayload%&lang=%lang%';

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /**
     * @param HttpAdapterInterface              $httpAdapter
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     */
    public function __construct(
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->extraParameterRepository = $extraParameterRepository;
    }

    /**
     * @param VianeoApiCall $vianeoApiCall
     *
     * @throws VianeoApiServerException
     */
    public function handle(VianeoApiCall $vianeoApiCall)
    {
        $sheet = $vianeoApiCall->sheet;
        $event = $sheet->getEvent();

        $vianeoEndpointParameter = 'https://techinnov.vianeo.io/index.php';
        $vianeoSharedSecretParameter  = 'xyxyxyxyxyxyxyx';
        $vianeoOptionParameter = 'com_vianeo_select_concours';
        $vianeoTaskParameter = 'techinnov.createaccount';

        // Payload building
        $payload = [

        ];

        // build de URL
        $urlWithData = strtr(
            self::URL_TEMPLATE,
            [
                '%endpoint%' => $vianeoEndpointParameter,
                '%option%' => $vianeoOptionParameter,
                '%task%' => $vianeoTaskParameter,
                '%sharedsecret%' => $vianeoSharedSecretParameter,
                '%jsonpayload%' => urlencode(json_encode($payload)),
                '%lang%' => self::DEFAULT_LOCALE
            ]
        );

        try {
            $response = $this->httpAdapter->get($urlWithData);
            dump($response);
        } catch (ServerErrorException $exception) {
            dump($exception);
            throw new VianeoApiServerException($exception);
        }
    }
}
