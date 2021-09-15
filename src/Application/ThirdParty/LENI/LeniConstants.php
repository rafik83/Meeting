<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class LeniConstants
{
    public const LENI_HOST = 'gateway.svc.exhibis.net';
    public const LENI_APP = 'O';
    public const LENI_MODE = 'MessageAndModifiedData';

    public const LENI_RESULTS = 'results';

    public const LENI_IS_VALID = 'IsValid';
    public const LENI_FIELD_INFO = 'Info';
    public const LENI_FIELD_VALUE = 'Value';
    public const LENI_FIELD_HAS_WARNING = 'HasWarning';

    public const GENDER_MAPPING = [
        Gender::MAN => 'M',
        Gender::WOMAN => 'MME',
    ];

    public const ATTENDANCE = 'Inscrit';

    public const LONG_FIELD = 255;
    public const MEDIUM_FIELD = 100;
    public const SHORT_FIELD = 50;

    public const LENI_COL_USER_ID = 'Id';
    public const LENI_COL_CAB_2 = 'Cab2';
    public const LENI_COL_EXTERNAL_KEY = 'CleExterne';
    public const LENI_COL_COMPANY_NAME = 'Societe';
    public const LENI_COL_CATEGORY = 'CategorieIndividuEvt';
    public const LENI_COL_TITLE = 'Civilite';
    public const LENI_COL_FIRST_NAME = 'Prenom';
    public const LENI_COL_LAST_NAME = 'Nom';
    public const LENI_COL_POSITION = 'Fonction';
    public const LENI_COL_PHONE_NUMBER = 'TelephoneFixe';
    public const LENI_COL_EMAIL = 'Email';
    public const LENI_COL_ADDRESS = 'Adresse1';
    public const LENI_COL_ZIPCODE = 'CodePostal';
    public const LENI_COL_CITY = 'Ville';
    public const LENI_COL_MOBILE = 'Mobile';
    public const LENI_COL_MOBILE_PHONE = 'TelephoneMobile';
    public const LENI_COL_EVENT_POSITION = 'EvenementFonction';
    public const LENI_COL_UNALLOCATED = 'ZL_RDVNONORGANISES';
    public const LENI_COL_DAY_FORMAT = 'ZL_JOURNEE%d';
    public const LENI_COL_COUNTRY = 'Pays';
    public const LENI_COL_ATTENDANCE = 'Inscrit';
    public const LENI_COL_LOCALE = 'Langue';
    public const LENI_COL_CREATED_AT = 'CreeLe';
    public const LENI_COL_UPDATED_AT = 'ModifieLe';
    public const LENI_COL_SENDING_REQUEST = 'SendingRequests';

    public const LENI_COL_EVENT_ORIGIN = 'EvenementOrigine';
    public const NEW_USER_EVENT_ORIGIN = 'API';

    public const LENI_COL_PARTICIPANT_PRODUCT_ID = 'ZL_IDPRODUITPARTICIPANT';

    public const LENI_COL_ENABLED = 'ZL_ACTIF';

    public const LENI_ENABLED_MAPPING = [
        true => 'ACTI',
        false => 'INAC',
    ];

    public const LENI_COL_IS_PAID = 'ZL_ETATDEPAIEMENT';

    public const LENI_IS_PAID_MAPPING = [
        true => 'PA',
        false => 'PP',
    ];

    public const LENI_LEADER_ID = 'ZL_LEADER_ID';
    public const LENI_LEADER_SHEET_NAME = 'ZL_LEADER_SOCIETE';
    public const LENI_LEADER_EMAIL = 'ZL_LEADER_EMAIL';
    public const LENI_LEADER_LAST_NAME = 'ZL_LEADER_NOM';
    public const LENI_LEADER_FIRST_NAME = 'ZL_LEADER_PRENOM';

    /**
     * Fields used in the GET Api
     */
    public const LENI_GET_FIELDS = [
        self::LENI_COL_USER_ID,
        self::LENI_COL_CATEGORY,
        self::LENI_COL_COMPANY_NAME,
        self::LENI_COL_TITLE,
        self::LENI_COL_FIRST_NAME,
        self::LENI_COL_LAST_NAME,
        self::LENI_COL_EVENT_POSITION,
        self::LENI_COL_EMAIL,
        self::LENI_COL_PHONE_NUMBER,
        self::LENI_COL_MOBILE,
        self::LENI_COL_MOBILE_PHONE,
        self::LENI_COL_ADDRESS,
        self::LENI_COL_ZIPCODE,
        self::LENI_COL_CITY,
        self::LENI_COL_COUNTRY,
        self::LENI_COL_LOCALE,
        self::LENI_COL_CREATED_AT,
        self::LENI_COL_UPDATED_AT,
    ];

    public const SHEET_STATE_MAPPING = [
        Sheet::STATE_PENDING => 'W',
        Sheet::STATE_VALIDATED => 'Y',
        Sheet::STATE_ACCEPTED => 'A',
        Sheet::STATE_REFUSED => 'R',
    ];

    public const SENDING_REQUEST_NEW_USER = 'sending_request_new_user';
    public const SENDING_REQUEST_SHEET_IS_VALIDATED = 'sending_request_sheet_is_validated';

    public const FILTER_FIELD = 'selectedFieldId';
    public const FILTER_VALUE = 'value';
    public const FILTER_OPERATOR = 'selectedOperator';
    public const FILTER_OPERATOR_GREATER_OR_EQUAL = 'GREATER_OR_EQUAL';
    public const FILTER_OPERATOR_IN = 'IN';

    public const SORT_ASC = 'ASC';

    public const DATA_MAPPING_FORMAT_TAGS = 'tags';
    public const DATA_MAPPING_FORMAT_PRODUCTS = 'products';
    public const DATA_MAPPING_FORMAT_STATES = 'states';

    public const DATA_MAPPING_FORMAT = [
        self::DATA_MAPPING_FORMAT_PRODUCTS => [
            // product_id => leni_field_name
        ],
        self::DATA_MAPPING_FORMAT_STATES => [
            // sheet_state => leni_field_name
        ],
        self::DATA_MAPPING_FORMAT_TAGS => [
            // tag_1 => leni_field_name
            // tag_2 => leni_field_name
        ],
    ];

    public const LENI_MAPPING_BOOLEAN_TRUE = 'True';
    public const LENI_MAPPING_BOOLEAN_FALSE = 'False';
    public const LENI_MAPPING_BOOLEAN = [
        true => self::LENI_MAPPING_BOOLEAN_TRUE,
        false => self::LENI_MAPPING_BOOLEAN_FALSE,
    ];
}
