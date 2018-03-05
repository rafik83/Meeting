<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Normalizer;

use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\View\LeniUserView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LeniUserViewNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var LeniUserView $userView */
        $userView = $object;

        $data = [
            LeniConstants::LENI_COL_CAB_2 => (string) $userView->id,
            LeniConstants::LENI_COL_EXTERNAL_KEY => $userView->id,
            LeniConstants::LENI_COL_COMPANY_NAME => mb_substr($userView->sheetName, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_CATEGORY => (string) $userView->categoryId,
            LeniConstants::LENI_COL_TYPE => (string) $userView->typeId,
            LeniConstants::LENI_COL_TITLE => LeniConstants::GENDER_MAPPING[$userView->gender] ?? '',
            LeniConstants::LENI_COL_FIRST_NAME => mb_substr($userView->firstName, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_LAST_NAME => mb_substr($userView->lastName, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_POSITION => mb_substr($userView->position, 0, LeniConstants::SHORT_FIELD),
            LeniConstants::LENI_COL_EMAIL => mb_substr($userView->email, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_MOBILE_PHONE => mb_substr($userView->mobile, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_PHONE_NUMBER => mb_substr($userView->phone, 0, LeniConstants::LONG_FIELD),
            LeniConstants::LENI_COL_UNALLOCATED => $userView->planning->unallocated,
            LeniConstants::LENI_COL_COUNTRY => $userView->country,
            LeniConstants::LENI_COL_ATTENDANCE => LeniConstants::ATTENDANCE,
            LeniConstants::LENI_COL_LOCALE => $userView->locale,
            LeniConstants::LENI_COL_ENABLED => LeniConstants::LENI_ENABLED_MAPPING[$userView->enabled],
            LeniConstants::LENI_COL_IS_PAID => LeniConstants::LENI_IS_PAID_MAPPING[$userView->paid],
            LeniConstants::LENI_COL_PARTICIPANT_PRODUCT_ID => $userView->participantProductId,
        ];

        $dayNumber = 1;

        foreach ($userView->planning->days as $day) {
            $data[sprintf(LeniConstants::LENI_COL_DAY_FORMAT, $dayNumber)] = $day->planning;

            $dayNumber++;
        }

        // Set the previous LENI user id
        // Warning: always on end of the returned array
        if (null !== $userView->leniId) {
            $data[LeniConstants::LENI_COL_USER_ID] = $userView->leniId;
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
