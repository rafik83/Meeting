<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Normalizer\Api\Leni;

use Proximum\Vimeet\Application\View\Api\Leni\LeniUserView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LeniUserViewNormalizer implements NormalizerInterface
{
    const LENI_COL_PARTICIPANT_ID = 'participantId';
    const LENI_COL_COMPANY_NAME = 'companyName';
    const LENI_COL_TYPE = 'type';
    const LENI_COL_TITLE = 'title';
    const LENI_COL_FIRST_NAME = 'firstName';
    const LENI_COL_LAST_NAME = 'lastName';
    const LENI_COL_POSITION = 'position';
    const LENI_COL_PHONE_NUMBER = 'phoneNumber';
    const LENI_COL_EMAIL = 'email';
    const LENI_COL_MOBILE_PHONE = 'mobilePhone';
    const LENI_COL_UNALLOCATED = 'ZL_RDVNONORGANISES';
    const LENI_COL_DAY = 'ZL_JOURNEE';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        /** @var LeniUserView $userView */
        $userView = $object;
        $data = [
            self::LENI_COL_PARTICIPANT_ID => $userView->id,
            self::LENI_COL_COMPANY_NAME   => $userView->sheetName,
            self::LENI_COL_TYPE           => $userView->typeName,
            self::LENI_COL_TITLE          => $userView->gender,
            self::LENI_COL_FIRST_NAME     => $userView->firstName,
            self::LENI_COL_LAST_NAME      => $userView->lastName,
            self::LENI_COL_POSITION       => $userView->position,
            self::LENI_COL_EMAIL          => $userView->email,
            self::LENI_COL_MOBILE_PHONE   => $userView->mobile,
            self::LENI_COL_PHONE_NUMBER   => $userView->phone,
            self::LENI_COL_UNALLOCATED    => $userView->planning->unallocated,
        ];

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
