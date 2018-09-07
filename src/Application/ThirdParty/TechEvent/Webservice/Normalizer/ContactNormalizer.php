<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Normalizer;

use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter\BooleanConverter;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter\CountryConverter;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter\GenderConverter;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter\SeparatorConverter;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter\TelephoneConverter;

class ContactNormalizer
{
    public function normalize(array $contact, array $normalizerMapping): array
    {
        if (empty($normalizerMapping)) {
            return $contact;
        }

        $contactsNormalized = [];

        foreach ($contact as $key => $contactData) {
            if (isset($normalizerMapping[$key])) {
                $contactsNormalized[$key] = $this->convert($contactData, $normalizerMapping[$key]);

                continue;
            }

            $contactsNormalized[$key] = $contactData;
        }

        return $contactsNormalized;
    }

    private function convert(string $dataToConvert, string $normalizer)
    {
        switch ($normalizer) {
            case 'boolean':
                return BooleanConverter::convert($dataToConvert);
            case 'country':
                return CountryConverter::convert($dataToConvert);
            case 'gender':
                return GenderConverter::convert($dataToConvert);
            case 'telephone':
                return TelephoneConverter::convert($dataToConvert);
            case 'separator':
                return SeparatorConverter::convert($dataToConvert);
            default:
                return $dataToConvert;
        }
    }
}
