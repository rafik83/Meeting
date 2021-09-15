<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Normalizer;

use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeniUserView;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;

class LeniUserViewNormalizer
{
    public function normalize(LeniUserView $userView): array
    {
        $data = [
            LeniConstants::LENI_COL_CAB_2 => (string) $userView->id,
            LeniConstants::LENI_COL_EXTERNAL_KEY => $userView->id,
            LeniConstants::LENI_COL_COMPANY_NAME => self::mediumTruncate($userView->sheetName),
            LeniConstants::LENI_COL_TITLE => LeniConstants::GENDER_MAPPING[$userView->gender] ?? '',
            LeniConstants::LENI_COL_FIRST_NAME => self::longTruncate($userView->firstName),
            LeniConstants::LENI_COL_LAST_NAME => self::longTruncate($userView->lastName),
            LeniConstants::LENI_COL_POSITION => self::shortTruncate($userView->position),
            LeniConstants::LENI_COL_EMAIL => self::longTruncate($userView->email),
            LeniConstants::LENI_COL_MOBILE_PHONE => self::longTruncate($userView->mobile),
            LeniConstants::LENI_COL_PHONE_NUMBER => self::longTruncate($userView->phone),
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

            ++$dayNumber;
        }

        if (null !== $userView->leaderView) {
            $data[LeniConstants::LENI_LEADER_ID] = $userView->leaderView->leniUserId;
            $data[LeniConstants::LENI_LEADER_SHEET_NAME] = self::longTruncate($userView->leaderView->sheetName);
            $data[LeniConstants::LENI_LEADER_EMAIL] = self::longTruncate($userView->leaderView->email);
            $data[LeniConstants::LENI_LEADER_LAST_NAME] = self::longTruncate($userView->leaderView->lastName ?? '');
            $data[LeniConstants::LENI_LEADER_FIRST_NAME] = self::longTruncate($userView->leaderView->firstName ?? '');
        }

        // Custom data
        foreach ($userView->customData as $fieldName => $value) {
            $data[$fieldName] = $value;
        }

        if (null !== $userView->leniId) {
            // Set the previous LENI user id
            $data[LeniConstants::LENI_COL_USER_ID] = $userView->leniId;
        }

        return array_filter($data, function ($value) {
            return null !== $value;
        });
    }

    private static function longTruncate(string $input): string
    {
        return mb_substr($input, 0, LeniConstants::LONG_FIELD);
    }

    private static function mediumTruncate(string $input): string
    {
        return mb_substr($input, 0, LeniConstants::MEDIUM_FIELD);
    }

    private static function shortTruncate(string $input): string
    {
        return mb_substr($input, 0, LeniConstants::SHORT_FIELD);
    }
}
