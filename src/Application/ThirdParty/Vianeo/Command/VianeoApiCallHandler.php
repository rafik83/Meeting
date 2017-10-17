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
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Exception\VianeoSheetAlreadyRegisteredException;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Exception\VianeoSheetNotRegisteredException;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class VianeoApiCallHandler
{
    /**
     * Example:
     * https://techinnov.vianeo.io/index.php?option=com_vianeo_select_concours&task=techinnov.createaccount&sharedsecret=xxxxx
     */
    const URL_TEMPLATE = '%endpoint%&jsonpayload=%jsonpayload%&lang=%lang%';
    const DEFAULT_LOCALE = 'fr';

    const VIANEO_RETURN_CODE = 'returnCode';
    const VIANEO_ERROR_MESSAGE = 'errorMessage';

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var VianeoGetSheetDataHandler */
    private $vianeoGetSheetDataHandler;

    /**
     * @param HttpAdapterInterface              $httpAdapter
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param VianeoGetSheetDataHandler         $vianeoGetSheetDataHandler
     */
    public function __construct(
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        VianeoGetSheetDataHandler $vianeoGetSheetDataHandler
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->vianeoGetSheetDataHandler = $vianeoGetSheetDataHandler;
    }

    /**
     * @param VianeoApiCall $vianeoApiCall
     *
     * @throws VianeoApiServerException
     * @throws VianeoSheetNotRegisteredException
     */
    public function handle(VianeoApiCall $vianeoApiCall)
    {
        $sheet = $vianeoApiCall->sheet;
        $event = $sheet->getEvent();

        $vianeoEndpointParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_VIANEO_ENDPOINT);

        if (null === $vianeoEndpointParameter) {
            throw new \LogicException(
                'Can not call VianeoApiCallHandler::handle() if event has not VIANEO_ENDPOINT'
            );
        }

        $jsonPayload = $this->vianeoGetSheetDataHandler->handle(
            new VianeoGetSheetData($sheet, self::DEFAULT_LOCALE)
        );

        $urlWithData = strtr(
            self::URL_TEMPLATE,
            [
                '%endpoint%' => $vianeoEndpointParameter->getValue(),
                '%jsonpayload%' => urlencode($jsonPayload),
                '%lang%' => self::DEFAULT_LOCALE
            ]
        );

        try {
            $jsonResponse = $this->httpAdapter->get($urlWithData);
            $response = json_decode($jsonResponse->body, true);

            if (!array_key_exists(self::VIANEO_RETURN_CODE, $response)) {
                throw new VianeoApiServerException('Missing Vianeo return code');
            }

            $returnCode = (int) $response[self::VIANEO_RETURN_CODE];

            if ($returnCode !== 0) {
                if ($returnCode === -100) {
                    throw new VianeoApiServerException('User already exists with this email');
                } else {
                    if ($returnCode === -403) {
                        throw new VianeoApiServerException('Access denied to VIANEO. Check VIANEO_SHARED_SECRET');
                    }
                }

                throw new VianeoApiServerException(
                    $response[self::VIANEO_ERROR_MESSAGE] ?? 'Server error with no message'
                );
            }
        } catch (ServerErrorException $exception) {
            throw new VianeoApiServerException($exception);
        }
    }
}
