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
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\MissingIdException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\WarningApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Normalizer\LeniUserViewNormalizer;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;

/**
 * LENI EXHIBIS Api call handler
 */
class LeniApiCallHandler
{
    const LENI_ENDPOINT = 'https://gateway.svc.exhibis.net/proximum/domain/IndividuEvt/Save/';
    const LENI_HOST = 'gateway.svc.exhibis.net';
    const LENI_APP = 'O';
    const LENI_MODE = 'MessageAndModifiedData';

    const LENI_IS_VALID = 'IsValid';
    const LENI_FIELD_INFO = 'Info';
    const LENI_FIELD_VALUE = 'Value';
    const LENI_FIELD_HAS_WARNING = 'HasWarning';

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param HttpAdapterInterface              $httpAdapter
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     */
    public function __construct(
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * @param LeniApiCall $leniApiCall
     *
     * @throws LeniApiServerException
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     * @throws MissingIdException
     */
    public function handle(LeniApiCall $leniApiCall)
    {
        $extraData = $leniApiCall->extraData;
        $event = $extraData->getEvent();

        $leniUserParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);

        if (null === $leniUserParameter || null === $leniEventParameter) {
            throw new \LogicException(
                'Can not call PrepareLeniApiCallHandler if event has not LENI_USER and LENI_EVENT'
            );
        }

        $data = unserialize($extraData->getValue());

        $body = json_encode(
            [
                'idEvt'  => $leniEventParameter->getValue(),
                'idUser' => $leniUserParameter->getValue(),
                'mode'   => self::LENI_MODE,
                'app'    => self::LENI_APP,
                'data'   => $data
            ]
        );

        $headers = [
            'Authorization'  => 'Basic ' . base64_encode($leniUserParameter->getValue()),
            'Host'           => self::LENI_HOST,
            'Content-Type'   => 'application/json',
            'Content-Length' => strlen($body),
            'Connection'     => 'Close',
        ];

        try {
            $jsonResponse = $this->httpAdapter->post(self::LENI_ENDPOINT, $headers, $body);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        $response = json_decode($jsonResponse->body, true);

        // Call not valid
        if (!isset($response[self::LENI_IS_VALID]) || $response[self::LENI_IS_VALID] !== true) {
            throw new NotValidApiCallException('LENI responded has a non valid response ' . $jsonResponse->body);
        }

        // When inserting user (first call) we must retrieve the LENI user id
        if (!isset($data[LeniUserViewNormalizer::LENI_COL_USER_ID])
            && (
                !isset($response[self::LENI_FIELD_INFO])
                || !isset($response[self::LENI_FIELD_INFO][LeniUserViewNormalizer::LENI_COL_USER_ID])
                || !isset($response[self::LENI_FIELD_INFO][LeniUserViewNormalizer::LENI_COL_USER_ID][self::LENI_FIELD_VALUE])
            )
        ) {
            throw new MissingIdException();
        }

        // Call has warnings
        if (isset($response[self::LENI_FIELD_HAS_WARNING])
            && $response[self::LENI_FIELD_HAS_WARNING] === true
            && isset($response[self::LENI_FIELD_INFO])
        ) {
            $warnings = [];

            foreach ($response[self::LENI_FIELD_INFO] as $key => $info) {
                if (in_array($key, LeniUserViewNormalizer::LENI_COLUMNS)) {
                    $warnings[] = $key;
                }
            }

            if (!empty($warnings)) {
                throw new WarningApiCallException($warnings);
            }
        }

        // Get and save the LENI user Id when it is the first call (insert user into LENI)
        if (!isset($data[LeniUserViewNormalizer::LENI_COL_USER_ID])) {
            $leniUserId = $response[self::LENI_FIELD_INFO][LeniUserViewNormalizer::LENI_COL_USER_ID][self::LENI_FIELD_VALUE];
            $data[LeniUserViewNormalizer::LENI_COL_USER_ID] = $leniUserId;

            $extraData->update(serialize($data), $extraData->getUpdatedAt());
            $this->extraDataRepository->set($extraData);
        }
    }
}
