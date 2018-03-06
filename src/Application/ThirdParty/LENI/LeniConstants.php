<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI;

use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class LeniConstants
{
    public const LENI_SAVE_ENDPOINT = 'https://gateway.svc.exhibis.net/proximum/domain/IndividuEvt/Save/';
    public const LENI_GET_ENDPOINT = 'https://gateway.svc.exhibis.net/proximum/domain/IndividuEvt/Save/';
    public const LENI_HOST = 'gateway.svc.exhibis.net';
    public const LENI_APP = 'O';
    public const LENI_MODE = 'MessageAndModifiedData';

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
    public const SHORT_FIELD = 50;

    public const LENI_COL_USER_ID = 'Id';
    public const LENI_COL_CAB_2 = 'Cab2';
    public const LENI_COL_EXTERNAL_KEY = 'CleExterne';
    public const LENI_COL_COMPANY_NAME = 'Societe';
    public const LENI_COL_TYPE = 'ZL_SOUSCATEGORIE';
    public const LENI_COL_CATEGORY = 'CategorieIndividuEvt';
    public const LENI_COL_TITLE = 'Civilite';
    public const LENI_COL_FIRST_NAME = 'Prenom';
    public const LENI_COL_LAST_NAME = 'Nom';
    public const LENI_COL_POSITION = 'Fonction';
    public const LENI_COL_PHONE_NUMBER = 'TelephoneFixe';
    public const LENI_COL_EMAIL = 'Email';
    public const LENI_COL_MOBILE_PHONE = 'TelephoneMobile';
    public const LENI_COL_UNALLOCATED = 'ZL_RDVNONORGANISES';
    public const LENI_COL_DAY_FORMAT = 'ZL_JOURNEE%d';
    public const LENI_COL_COUNTRY = 'Pays';
    public const LENI_COL_ATTENDANCE = 'Inscrit';
    public const LENI_COL_LOCALE = 'Langue';

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

    public const LENI_COLUMNS = [
        self::LENI_COL_USER_ID,
        self::LENI_COL_CAB_2,
        self::LENI_COL_EXTERNAL_KEY,
        self::LENI_COL_COMPANY_NAME,
        self::LENI_COL_TYPE,
        self::LENI_COL_CATEGORY,
        self::LENI_COL_TITLE,
        self::LENI_COL_FIRST_NAME,
        self::LENI_COL_LAST_NAME,
        self::LENI_COL_POSITION,
        self::LENI_COL_PHONE_NUMBER,
        self::LENI_COL_EMAIL,
        self::LENI_COL_MOBILE_PHONE,
        self::LENI_COL_UNALLOCATED,
        self::LENI_COL_COUNTRY,
        self::LENI_COL_ATTENDANCE,
        self::LENI_COL_LOCALE,
        self::LENI_COL_PARTICIPANT_PRODUCT_ID,
        self::LENI_COL_ENABLED,
        self::LENI_COL_IS_PAID,
    ];
}
