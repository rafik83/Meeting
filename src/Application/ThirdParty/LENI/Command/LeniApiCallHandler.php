<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Command;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\WarningApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Normalizer\LeniUserViewNormalizer;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

/**
 * LENI EXHIBIS Api call handler
 */
class LeniApiCallHandler
{
    const LENI_ENDPOINT = 'https://gateway.svc.exhibis.net/proximum/domain/IndividuEvt/Save/';
    const LENI_HOST = 'gateway.svc.exhibis.net';
    const LENI_APP = 'O';
    const LENI_MODE = 'MessageAndModifiedData';

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /**
     * @param HttpAdapterInterface $httpAdapter
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
     * @param LeniApiCall $leniApiCall
     *
     * @throws LeniApiServerException
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     */
    public function handle(LeniApiCall $leniApiCall)
    {
        $event = $leniApiCall->extraData->getEvent();
        $leniUserParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);

        if (null === $leniUserParameter || null === $leniEventParameter) {
            throw new \LogicException(
                'Can not call PrepareLeniApiCallHandler if event has not LENI_USER and LENI_EVENT'
            );
        }

        $body = json_encode(
            [
                'idEvt'  => $leniEventParameter,
                'idUser' => $leniUserParameter,
                'mode'   => self::LENI_MODE,
                'app'    => self::LENI_APP,
                'data'   => $command->leniUserView->serializeContent,
            ]
        );

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
