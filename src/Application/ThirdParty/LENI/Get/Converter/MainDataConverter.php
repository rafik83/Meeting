<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class MainDataConverter
{
    private const GENDER_MAPPING = [
        'MME' => Gender::WOMAN,
        'MLLE' => Gender::WOMAN,
        'M' => Gender::MAN,
        'MR' => Gender::MAN,
    ];

    /**
     * @param array $rawUser
     *
     * @return array indexed by tag
     */
    public function convert(array $rawUser): array
    {
        return [
            Tag::SHEET_TITLE => $this->getSheetTitle($rawUser),
            Tag::SHEET_ORGANIZATION => $rawUser[LeniConstants::LENI_COL_COMPANY_NAME] ?? null,
            Tag::SHEET_ADDRESS => $rawUser[LeniConstants::LENI_COL_ADDRESS] ?? null,
            Tag::SHEET_ZIPCODE => $rawUser[LeniConstants::LENI_COL_ZIPCODE] ?? null,
            Tag::SHEET_CITY => $rawUser[LeniConstants::LENI_COL_CITY] ?? null,
            Tag::SHEET_COUNTRY => $rawUser[LeniConstants::LENI_COL_COUNTRY] ?? null,
            Tag::PARTICIPANT_GENDER => $this->getGender($rawUser),
            Tag::PARTICIPANT_FIRSTNAME => $rawUser[LeniConstants::LENI_COL_FIRST_NAME] ?? null,
            Tag::PARTICIPANT_LASTNAME => $rawUser[LeniConstants::LENI_COL_LAST_NAME] ?? null,
            Tag::PARTICIPANT_MOBILE => $rawUser[LeniConstants::LENI_COL_MOBILE_PHONE] ?? null,
            Tag::PARTICIPANT_PHONE => $rawUser[LeniConstants::LENI_COL_PHONE_NUMBER] ?? null,
            Tag::PARTICIPANT_ADDRESS => $rawUser[LeniConstants::LENI_COL_ADDRESS] ?? null,
            Tag::PARTICIPANT_ZIPCODE => $rawUser[LeniConstants::LENI_COL_ZIPCODE] ?? null,
            Tag::PARTICIPANT_CITY => $rawUser[LeniConstants::LENI_COL_CITY] ?? null,
            Tag::PARTICIPANT_COUNTRY => $rawUser[LeniConstants::LENI_COL_COUNTRY] ?? null,
            Tag::PARTICIPANT_POSITION => $rawUser[LeniConstants::LENI_COL_EVENT_POSITION] ?? null,
        ];
    }

    /**
     * @param array $rawUser
     *
     * @return null|string
     */
    private function getGender(array &$rawUser): ?string
    {
        return isset($rawUser[LeniConstants::LENI_COL_TITLE])
            ? (self::GENDER_MAPPING[$rawUser[LeniConstants::LENI_COL_TITLE]] ?? null)
            : null;
    }

    /**
     * @param array $rawUser
     *
     * @return string
     */
    private function getSheetTitle(array &$rawUser): string
    {
        if (isset($rawUser[LeniConstants::LENI_COL_COMPANY_NAME])
            && '' !== trim($rawUser[LeniConstants::LENI_COL_COMPANY_NAME])
        ) {
            return $rawUser[LeniConstants::LENI_COL_COMPANY_NAME];
        }

        if (isset($rawUser[LeniConstants::LENI_COL_FIRST_NAME], $rawUser[LeniConstants::LENI_COL_LAST_NAME])) {
            $completeName = sprintf(
                '%s %s',
                $rawUser[LeniConstants::LENI_COL_LAST_NAME],
                $rawUser[LeniConstants::LENI_COL_FIRST_NAME]
            );

            if ('' !== trim($completeName)) {
                return $completeName;
            }
        }

        return $rawUser[LeniConstants::LENI_COL_EMAIL];
    }
}
