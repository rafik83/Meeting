<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\WarningApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;

class LeniApiCaller
{
    private const CONTENT_TYPE_APPLICATION_JSON = 'application/json';
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
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     */
    public function save(Event $event, array $data): Object
    {
        $leniUserParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);
        $leniModeParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_MODE);
        $leniEndpointParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_LENI_SAVE_ENDPOINT
        );

        $isAuthorizedMode = $leniModeParameter !== null
            && \in_array(
                $leniModeParameter->getValue(),
                [
                    Type::VALUE_LENI_MODE_SAVE,
                    Type::VALUE_LENI_MODE_BOTH,
                ],
                true
            );

        if (!$isAuthorizedMode
            || null === $leniUserParameter
            || null === $leniEventParameter
            || null === $leniEndpointParameter
        ) {
            throw new \LogicException(
                'Can not call LeniApiCaller because event has not LENI_USER, LENI_EVENT, valid LENI_MODE or LENI Save endpoint'
            );
        }

        $body = json_encode(
            [
                'idEvt' => $leniEventParameter->getValue(),
                'idUser' => $leniUserParameter->getValue(),
                'mode' => LeniConstants::LENI_MODE,
                'app' => LeniConstants::LENI_APP,
                'data' => $data,
            ]
        );

        $headers = $this->getHeaders($leniUserParameter->getValue(), $body);

        try {
            $jsonResponse = $this->httpAdapter->post($leniEndpointParameter->getValue(), $headers, $body);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        $response = json_decode($jsonResponse->body, true);

        if (!isset($response[LeniConstants::LENI_IS_VALID]) || $response[LeniConstants::LENI_IS_VALID] !== true) {
            throw new NotValidApiCallException(sprintf(
                "Headers: %s;\nRequest: %s;\nResponse: %s;",
                json_encode($headers),
                $body,
                $jsonResponse->body
            ));
        }

        return $response;
    }

    /**
     * @param Event $event
     *
     * @return array
     *
     * @throws \LogicException
     * @throws LeniApiServerException
     */
    public function get(Event $event): array
    {
        $leniUserParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);
        $leniModeParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_MODE);
        $leniEndpointParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            Type::TYPE_LENI_GET_ENDPOINT
        );

        $isAuthorizedMode = $leniModeParameter !== null
            && \in_array($leniModeParameter->getValue(),
                [
                    Type::VALUE_LENI_MODE_GET,
                    Type::VALUE_LENI_MODE_BOTH,
                ],
                true
            );

        if (!$isAuthorizedMode
            || null === $leniUserParameter
            || null === $leniEventParameter
            || null === $leniEndpointParameter
        ) {
            throw new \LogicException(
                'Can not call LeniApiCaller because event has not LENI_USER, LENI_EVENT, valid LENI_MODE or LENI Get endpoint'
            );
        }

        $body = $this->getBody($leniEventParameter->getValue());
        $headers = $this->getHeaders($leniUserParameter->getValue(), $body);

        try {
            $jsonResponse = $this->httpAdapter->post($leniEndpointParameter->getValue(), $headers, $body);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        return json_decode($jsonResponse->body, true);
    }

    private function getBody(string $idEvt): string
    {
        return json_encode(
            [
                'idEvt' => $idEvt,
                'filters' => [],
                'fields' => [
                    'Id',
                    'Cab1',
                    'Adresse1',
                    'Adresse3',
                    'Societe',
                    'Adresse2',
                    'CodePostal',
                    'Ville',
                    'Pays',
                    'Civilite',
                    'Nom',
                    'Prenom',
                    'EvenementFonction',
                    'TelephoneFixe',
                    'Email',
                    'Mobile',
                    'Inscrit',
                    'CategorieIndividuEvt',
                    'ZL_PROFIL',
                    'ZL_ACTIVITE',
                    'ZL_TypePrestation',
                    'ZL_AUTREPRESTATION',
                    'ZL_Effectif',
                    'ZL_Commerce',
                    'ZL_Emplacement',
                    'ZL_NombreHabitant',
                    'ZL_AUTRE_FONTION',
                    'ZL_Age',
                    'ZL_ConnuSalon',
                    'ZL_CONFIRMATION',
                    'ZL_ATTENTES',
                    'ZL_THEMATIQUES',
                    'CreeLe',
                    'ModifieLe',
                ],
                'start' => 0,
                'take' => 1,
            ]
        );
    }

    private function getHeaders(string $authorizedUser, string $body): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($authorizedUser),
            'Host' => LeniConstants::LENI_HOST,
            'Content-Type' => self::CONTENT_TYPE_APPLICATION_JSON,
            'Content-Length' => mb_strlen($body),
            'Connection' => self::CLOSE_CONNECTION,
        ];
    }
}
