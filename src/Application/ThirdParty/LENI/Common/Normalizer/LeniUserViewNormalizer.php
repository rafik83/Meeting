<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Normalizer;

use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeniUserView;

class LeniUserViewNormalizer
{
    public function normalize(LeniUserView $userView): array
    {
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

        if ($userView->leaderView !== null) {
            $data[LeniConstants::LENI_LEADER_ID] = $userView->leaderView->leniUserId;
            $data[LeniConstants::LENI_LEADER_SHEET_NAME] = mb_substr($userView->leaderView->sheetName, 0, LeniConstants::LONG_FIELD);
            $data[LeniConstants::LENI_LEADER_EMAIL] = mb_substr($userView->leaderView->email, 0, LeniConstants::LONG_FIELD);
            $data[LeniConstants::LENI_LEADER_LAST_NAME] = mb_substr($userView->leaderView->lastName, 0, LeniConstants::LONG_FIELD);
            $data[LeniConstants::LENI_LEADER_FIRST_NAME] = mb_substr($userView->leaderView->firstName, 0, LeniConstants::LONG_FIELD);
        }

        // Custom data
        foreach ($userView->customData as $fieldName => $value) {
            $data[$fieldName] = $value;
        }

        if (null !== $userView->leniId) {
            // Set the previous LENI user id
            $data[LeniConstants::LENI_COL_USER_ID] = $userView->leniId;
        }

        return array_filter($data, function($value) {
            return null !== $value;
        });
    }
}
