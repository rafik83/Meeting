<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Normalizer;

use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniUserView;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LeniUserViewNormalizer implements NormalizerInterface
{
    const LONG_FIELD = 255;
    const SHORT_FIELD = 50;

    const LENI_COL_USER_ID      = 'Id';
    const LENI_COL_CAB_2        = 'Cab2';
    const LENI_COL_EXTERNAL_KEY = 'CleExterne';
    const LENI_COL_COMPANY_NAME = 'Societe';
    const LENI_COL_TYPE         = 'ZL_SOUSCATEGORIE';
    const LENI_COL_CATEGORY     = 'CategorieIndividuEvt';
    const LENI_COL_TITLE        = 'Civilite';
    const LENI_COL_FIRST_NAME   = 'Prenom';
    const LENI_COL_LAST_NAME    = 'Nom';
    const LENI_COL_POSITION     = 'Fonction';
    const LENI_COL_PHONE_NUMBER = 'TelephoneFixe';
    const LENI_COL_EMAIL        = 'Email';
    const LENI_COL_MOBILE_PHONE = 'TelephoneMobile';
    const LENI_COL_UNALLOCATED  = 'ZL_RDVNONORGANISES';
    const LENI_COL_DAY          = 'ZL_JOURNEE';
    const LENI_COL_COUNTRY      = 'Pays';
    const LENI_COL_ATTENDANCE   = 'Inscrit';
    const LENI_COL_LOCALE       = 'Langue';

    const LENI_COLUMNS = [
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
        self::LENI_COL_DAY,
        self::LENI_COL_COUNTRY,
        self::LENI_COL_ATTENDANCE,
        self::LENI_COL_LOCALE,
    ];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var LeniUserView $userView */
        $userView = $object;

        $data = [
            self::LENI_COL_CAB_2        => (string) $userView->id,
            self::LENI_COL_EXTERNAL_KEY => $userView->id,
            self::LENI_COL_COMPANY_NAME => mb_substr($userView->sheetName, 0, self::LONG_FIELD),
            self::LENI_COL_CATEGORY     => (string) $userView->categoryId,
            self::LENI_COL_TYPE         => (string) $userView->typeId,
            self::LENI_COL_TITLE        => $userView->gender,
            self::LENI_COL_FIRST_NAME   => mb_substr($userView->firstName, 0, self::LONG_FIELD),
            self::LENI_COL_LAST_NAME    => mb_substr($userView->lastName, 0, self::LONG_FIELD),
            self::LENI_COL_POSITION     => mb_substr($userView->position, 0, self::SHORT_FIELD),
            self::LENI_COL_EMAIL        => mb_substr($userView->email, 0, self::LONG_FIELD),
            self::LENI_COL_MOBILE_PHONE => mb_substr($userView->mobile, 0, self::LONG_FIELD),
            self::LENI_COL_PHONE_NUMBER => mb_substr($userView->phone, 0, self::LONG_FIELD),
            self::LENI_COL_UNALLOCATED  => $userView->planning->unallocated,
            self::LENI_COL_COUNTRY      => $userView->country,
            self::LENI_COL_ATTENDANCE   => $userView->attendance,
            self::LENI_COL_LOCALE       => $userView->locale,
        ];

        // Set the previous LENI user id
        if ($context['previousUserExtraData'] instanceof ExtraData) {
            $previousData = unserialize($context['previousUserExtraData']->getValue());
            $data[self::LENI_COL_USER_ID] = $previousData[self::LENI_COL_USER_ID];
        }

        $dayNumber = 1;
        foreach ($userView->planning->days as $day) {
            $data[self::LENI_COL_DAY . $dayNumber] = $day->planning;

            $dayNumber++;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof LeniUserView;
    }
}
