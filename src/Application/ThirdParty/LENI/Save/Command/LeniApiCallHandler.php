<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\MissingIdException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\WarningApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

/**
 * LENI EXHIBIS Api call handler
 *
 * Documentation in french:
 *
 * // Save : Création d'un Individu dans ATLAS
 * // création car pas d'Id spécifié lors de l’appel de Save
 * // on peut aussi créer un Individu en spécifiant un Id inconnu de ATLAS
 * data = {
 *   "idEvt" : "'. $IdEvt .'",
 *   "data" : {
 *     "actionFlag": "0",
 *     "Civilite": "Mr",
 *     "Prenom": "Pascal",
 *     "Nom": "LENI002",
 *     "Societe": "TEST",
 *     "Adresse1": "TEST",
 *     "CodePostal": "93100",
 *     "Ville": "MONTREUIL",
 *     "Pays": "FR",
 *     "CleExterne": "656477187"
 *   }
 * }
 *
 * Le WebService renvoie dans le buffer de retour certaines informations
 * et l'information Id doit être récupérée et être sauvegardée dans la base de données du client
 * afin de pouvoir rappeler save en mode Update
 *
 * Buffer de retour :
 * {
 *   "IsValid":true,
 *   "HasWarning":true,
 *   "Info":{
 *   "CreeLe":{
 *   "Info":"ModifiedData",
 *     "Value":"\/Date(1507732534642)\/"
 *     },
 *     "ModifieLe":{
 *       "Info":"ModifiedData",
 *       "Value":"\/Date(1507732534642)\/"
 *     },
 *     "OrigineCreation":{
 *       "Info":"ModifiedData",
 *       "Value":"X"
 *     },
 *     "Npai":{
 *       "Info":"ModifiedData",
 *       "Value":false
 *     },
 *     "CptNpai":{
 *       "Info":"ModifiedData",
 *       "Value":0
 *     },
 *     "RegionAdministrative":{
 *       "Info":"ModifiedData",
 *       "Value":"11"
 *     },
 *     "Departement":{
 *       "Info":"ModifiedData",
 *       "Value":"93"
 *     },
 *     "Cab1":{
 *       "Info":"ModifiedData",
 *       "Value":"X46Z7UXFC"
 *     },
 *     "Login":{
 *       "Info":"ModifiedData",
 *       "Value":"CaYBB2"
 *     },
 *     "Password":{
 *       "Info":"ModifiedData",
 *       "Value":"WUyK68"
 *     },
 *     "Id":{
 *       "Info":"ModifiedData",
 *       "Value":"d451756d-91ae-e711-80e2-005056ae4dce"
 *     }
 *   },
 *   "version":"Beta",
 *   "versionNumber":"002.021.000",
 *   "lastDescriptionModification":"\/Date(1507732534626)\/"
 * }
 *
 *
 * Même appel de save mais en mode Update en spécifiant l'Id
 *
 * data = {
 *   "idEvt" : "'. $IdEvt .'",
 *   "data" : {
 *     "Id" : "d451756d-91ae-e711-80e2-005056ae4dce",
 *     "actionFlag": "0",
 *     "Civilite": "Mr",
 *     "Prenom": "Pascal",
 *     ...
 *   }
 * }
 */
class LeniApiCallHandler
{
    /** @var HttpAdapterInterface */
    private $httpAdapter;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param HttpAdapterInterface              $httpAdapter
     * @param ExtraParameterRepositoryInterface $extraParameterRepository
     * @param ExtraDataRepositoryInterface      $extraDataRepository
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        HttpAdapterInterface $httpAdapter,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->httpAdapter = $httpAdapter;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param LeniApiCall $leniApiCall
     *
     * @throws \LogicException
     * @throws LeniApiServerException
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     * @throws MissingIdException
     */
    public function handle(LeniApiCall $leniApiCall): void
    {
        $pendingExtraData = $leniApiCall->extraData;

        if (ExtraDataType::LENI_FINGERPRINT_PENDING !== $pendingExtraData->getName()) {
            return;
        }

        $event = $pendingExtraData->getEvent();

        $leniUserParameter  = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_USER);
        $leniEventParameter = $this->extraParameterRepository->findByEventAndType($event, Type::TYPE_LENI_EVENT);

        if (null === $leniUserParameter || null === $leniEventParameter) {
            throw new \LogicException(
                'Can not call PrepareLeniApiCallHandler if event has not LENI_USER and LENI_EVENT'
            );
        }

        $data = unserialize($pendingExtraData->getValue(), ['allowed_classes' => false]);

        // remove data userId when null
        if (array_key_exists(LeniConstants::LENI_COL_USER_ID, $data)
            && null === $data[LeniConstants::LENI_COL_USER_ID]
        ) {
            unset($data[LeniConstants::LENI_COL_USER_ID]);
        }

        $body = json_encode(
            [
                'idEvt'  => $leniEventParameter->getValue(),
                'idUser' => $leniUserParameter->getValue(),
                'mode'   => LeniConstants::LENI_MODE,
                'app'    => LeniConstants::LENI_APP,
                'data'   => $data
            ]
        );

        $headers = [
            'Authorization'  => 'Basic ' . base64_encode($leniUserParameter->getValue()),
            'Host'           => LeniConstants::LENI_HOST,
            'Content-Type'   => 'application/json',
            'Content-Length' => mb_strlen($body),
            'Connection'     => 'Close',
        ];

        try {
            $jsonResponse = $this->httpAdapter->post(LeniConstants::LENI_ENDPOINT, $headers, $body);
        } catch (ServerErrorException $exception) {
            throw new LeniApiServerException($exception);
        }

        $response = json_decode($jsonResponse->body, true);

        $apiCallLog = sprintf(
            "Headers: %s;\nRequest: %s;\nResponse: %s;",
            json_encode($headers),
            $body,
            $jsonResponse->body
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

        $this->addOrUpdateFingerprint($pendingExtraData->getEvent(), $pendingExtraData->getUser(), serialize($data));
        $this->extraDataRepository->remove($pendingExtraData);
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $fingerPrint
     *
     * @return ExtraData
     */
    private function addOrUpdateFingerprint(Event $event, User $user, string $fingerPrint): ExtraData
    {
        $userExtraDataFingerprint = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $event,
            ExtraDataType::LENI_FINGERPRINT,
            $user
        );

        if ($userExtraDataFingerprint instanceof ExtraData) {
            $userExtraDataFingerprint->update($fingerPrint, $this->dateTime);
            $this->extraDataRepository->set($userExtraDataFingerprint);
        } else {
            $userExtraDataFingerprint = new ExtraData(
                $user,
                $event,
                ExtraDataType::LENI_FINGERPRINT,
                $fingerPrint,
                $this->dateTime
            );
            $this->extraDataRepository->add($userExtraDataFingerprint);
        }

        return $userExtraDataFingerprint;
    }
}
