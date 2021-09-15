<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command;

use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\UserExtraData\UserExtraDataFingerprintManager;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\MissingIdException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\NotValidApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\WarningApiCallException;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomDataHandler;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
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
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var LeniApiCaller */
    private $leniApi;

    /** @var UserExtraDataFingerprintManager */
    private $userExtraDataFingerprintManager;

    /** @var GetCustomDataHandler */
    private $getCustomDataHandler;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param ExtraDataRepositoryInterface    $extraDataRepository
     * @param LeniApiCaller                   $leniApi
     * @param UserExtraDataFingerprintManager $userExtraDataFingerprintManager
     * @param GetCustomDataHandler            $getCustomDataHandler
     * @param \DateTimeInterface              $dateTime
     */
    public function __construct(
        ExtraDataRepositoryInterface $extraDataRepository,
        LeniApiCaller $leniApi,
        UserExtraDataFingerprintManager $userExtraDataFingerprintManager,
        GetCustomDataHandler $getCustomDataHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->extraDataRepository = $extraDataRepository;
        $this->leniApi = $leniApi;
        $this->userExtraDataFingerprintManager = $userExtraDataFingerprintManager;
        $this->dateTime = $dateTime;
        $this->getCustomDataHandler = $getCustomDataHandler;
    }

    /**
     * @param LeniApiCall $leniApiCall
     *
     * @throws LeniApiServerException
     * @throws MissingIdException
     * @throws NotValidApiCallException
     * @throws WarningApiCallException
     * @throws \LogicException
     */
    public function handle(LeniApiCall $leniApiCall): void
    {
        $pendingExtraData = $leniApiCall->extraData;

        if (ExtraDataType::LENI_FINGERPRINT_PENDING !== $pendingExtraData->getName()) {
            return;
        }

        $event = $pendingExtraData->getEvent();
        $user = $pendingExtraData->getUser();

        $data = unserialize($pendingExtraData->getValue(), ['allowed_classes' => false]);

        // remove data userId when null
        if (array_key_exists(LeniConstants::LENI_COL_USER_ID, $data)
            && null === $data[LeniConstants::LENI_COL_USER_ID]
        ) {
            unset($data[LeniConstants::LENI_COL_USER_ID]);
        }

        $response = $this->leniApi->save(
            $event,
            $this->getCustomDataHandler->handle(new GetCustomData($event, $user, $data))
        );

        $hasNotUserId = !isset($data[LeniConstants::LENI_COL_USER_ID]);

        // When inserting user (first call) we must retrieve the LENI user id
        if ($hasNotUserId) {
            if (!isset($response[LeniConstants::LENI_FIELD_INFO][LeniConstants::LENI_COL_USER_ID][LeniConstants::LENI_FIELD_VALUE])) {
                throw new MissingIdException('Missing LENI userId');
            }

            // Get and save the LENI user Id when it is the first call (insert user into LENI)
            $leniUserId = $response[LeniConstants::LENI_FIELD_INFO][LeniConstants::LENI_COL_USER_ID][LeniConstants::LENI_FIELD_VALUE];

            if (null === $leniUserId) {
                throw new MissingIdException('LENI returned a null user id');
            }

            $data[LeniConstants::LENI_COL_USER_ID] = $leniUserId;

            $this->extraDataRepository->add(
                new ExtraData(
                    $leniApiCall->extraData->getUser(),
                    $leniApiCall->extraData->getEvent(),
                    Type::LENI_USER_ID,
                    $leniUserId,
                    $this->dateTime
                )
            );
        }

        $this->userExtraDataFingerprintManager->addOrUpdateFingerprint($event, $user, serialize($data));
        $this->extraDataRepository->remove($pendingExtraData);

        // Call has warnings
        if (isset($response[LeniConstants::LENI_FIELD_HAS_WARNING], $response[LeniConstants::LENI_FIELD_INFO])
            && true === $response[LeniConstants::LENI_FIELD_HAS_WARNING]
        ) {
            throw new WarningApiCallException(
                sprintf('Data : %s ; Response : %s', json_encode($data), json_encode($response))
            );
        }
    }
}
