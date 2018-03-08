<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\MissingIdException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\WarningApiCallException;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class LeniApiCaller
{
    private const CONTENT_TYPE = 'application/json';
    private const CLOSE_CONNECTION = 'Close';

    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    public function __construct(
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->extraParameterRepository = $extraParameterRepository;
    }

    /**
     * @param Event $event
     * @param mixed $data
     *
     * @return Object
     *
     * @throws \LogicException
     * @throws LeniApiServerException
     * @throws MissingIdException
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     */
    public function save(Event $event, mixed $data): Object
    {
        $authorizedModes = [Type::TYPE_LENI_MODE_SAVE_VALUE, Type::TYPE_LENI_MODE_BOTH_VALUE];

        $leniUserParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);
        $leniModeParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_MODE);

        $isAuthorizedMode = $leniModeParameter !== null && \in_array($leniModeParameter, $authorizedModes, true);

        if (!$isAuthorizedMode || null === $leniUserParameter || null === $leniEventParameter) {
            throw new \LogicException(
                'Can not call LeniApiCaller if event has not LENI_USER and LENI_EVENT and LENI_MODE'
            );
        }

        $body    = $this->getBody($leniEventParameter->getValue(), $leniUserParameter->getValue(), $data);
        $headers = $this->getHeaders($leniUserParameter->getValue(), $body);

        try {
            $jsonResponse = $this->httpAdapter->post(LeniConstants::LENI_SAVE_ENDPOINT, $headers, $body);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        $this->debugApiSaveCallErrors(json_decode($jsonResponse->body, true), $headers, $body, $data);

        return json_decode($jsonResponse->body, true);
    }

    /**
     * @param Event $event
     * @param mixed $data
     *
     * @return Object
     *
     * @throws \LogicException
     * @throws LeniApiServerException
     */
    public function get(Event $event, mixed $data): Object
    {
        $authorizedModes = [Type::TYPE_LENI_MODE_GET_VALUE, Type::TYPE_LENI_MODE_BOTH_VALUE];
        $leniUserParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);
        $leniModeParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_MODE);

        $isAuthorizedMode = $leniModeParameter !== null && \in_array($leniModeParameter, $authorizedModes, true);

        if (!$isAuthorizedMode || null === $leniUserParameter || null === $leniEventParameter) {
            throw new \LogicException(
                'Can not call LeniApiCaller if event has not LENI_USER and LENI_EVENT and LENI_MODE'
            );
        }

        $body    = $this->getBody($leniEventParameter->getValue(), $leniUserParameter->getValue(), $data);
        $headers = $this->getHeaders($leniUserParameter->getValue(), $body);

        try {
            $jsonResponse = $this->httpAdapter->post(LeniConstants::LENI_GET_ENDPOINT, $headers, $body);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        return json_decode($jsonResponse->body, true);
    }

    private function getBody(string $idEvt, string $idUser, mixed $data): string
    {
        // @todo en dur pour tester
        return json_encode(
            [
                'idEvt'  => $idEvt,
                'lang' => ['fr'],
                'app' => "B",
                'filters' => [
                    [
                        'selectedFieldId' => 'Inscrit',
                        'selectedOperator' => 'EQUAL',
                        'value' => 'Inscrit'
                    ],
                    [
                        'selectedFieldId' => 'CategorieIndividuEvt',
                        'selectedOperator' => 'IN',
                        'value' => 'VISITEUR'
                    ]
                ],
                'fields' => [
                    'Id',
                    'Cab1',
                    'ZL_PROFIL',
                    'Societe',
                    'Adresse1',
                    'Adresse2',
                    'Adresse3',
                    'CodePostal',
                    'Ville',
                    'Pays',
                    'TelephoneFixe',
                    'ZL_ACTIVITE',
                    'ZL_TypePrestation',
                    'ZL_AUTREPRESTATION',
                    'ZL_Effectif',
                    'ZL_Commerce',
                    'ZL_Emplacement',
                    'ZL_NombreHabitant',
                    'Civilite',
                    'Nom',
                    'Prenom',
                    'EvenementFonction',
                    'ZL_AUTRE_FONTION',
                    'Email',
                    'Mobile',
                    'ZL_Age',
                    'ZL_ConnuSalon',
                    'ZL_CONFIRMATION',
                    'ZL_ATTENTES',
                    'ZL_THEMATIQUES',
                    'CodeMarketing',
                    'Inscrit',
                    'CategorieIndividuEvt'
                ],
                'start' => 0,
                'take' => 20,
            ]
        );
    }

    private function getHeaders(string $authorizedUser, string $body): array
    {
        return [
            'Authorization'  => 'Basic ' . base64_encode($authorizedUser),
            'Host'           => LeniConstants::LENI_HOST,
            'Content-Type'   => self::CONTENT_TYPE,
            'Content-Length' => mb_strlen($body),
            'Connection'     => self::CLOSE_CONNECTION,
        ];
    }

    /**
     * @throws MissingIdException
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     */
    public function debugApiSaveCallErrors(array $response, array $headers, string $body, mixed $data): void
    {
        $apiCallLog = sprintf(
            "Headers: %s;\nRequest: %s;\nResponse: %s;",
            json_encode($headers),
            $body,
            $response
        );

        // Call not valid
        if (!isset($response[LeniConstants::LENI_IS_VALID]) || $response[LeniConstants::LENI_IS_VALID] !== true) {
            throw new NotValidApiCallException($apiCallLog);
        }

        $hasNotUserId = !isset($data[LeniConstants::LENI_COL_USER_ID])
                        || null === $data[LeniConstants::LENI_COL_USER_ID];

        // When inserting user (first call) we must retrieve the LENI user id
        if ($hasNotUserId
            && (
                !isset($response[LeniConstants::LENI_FIELD_INFO])
                || !isset($response[LeniConstants::LENI_FIELD_INFO][LeniConstants::LENI_COL_USER_ID])
                || !isset($response[LeniConstants::LENI_FIELD_INFO][LeniConstants::LENI_COL_USER_ID][LeniConstants::LENI_FIELD_VALUE])
            )
        ) {
            throw new MissingIdException($apiCallLog);
        }

        // Call has warnings
        if (isset($response[LeniConstants::LENI_FIELD_HAS_WARNING], $response[LeniConstants::LENI_FIELD_INFO])
            && $response[LeniConstants::LENI_FIELD_HAS_WARNING] === true
        ) {
            $warnings = [];

            foreach ($response[LeniConstants::LENI_FIELD_INFO] as $key => $info) {
                if (\in_array($key, LeniConstants::LENI_COLUMNS, true)) {
                    $warnings[] = $key;
                }
            }

            if (!empty($warnings)) {
                throw new WarningApiCallException($apiCallLog);
            }
        }

        // Get and save the LENI user Id when it is the first call (insert user into LENI)
        if ($hasNotUserId) {
            $leniUserId = $response[LeniConstants::LENI_FIELD_INFO][LeniConstants::LENI_COL_USER_ID][LeniConstants::LENI_FIELD_VALUE];

            if (null === $leniUserId) {
                throw new \LogicException('LENI returned a null user id: ' . $apiCallLog);
            }

            $data[LeniConstants::LENI_COL_USER_ID] = $leniUserId;
        }
    }
}
