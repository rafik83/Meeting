<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Normalizer;

use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter\BooleanConverter;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Converter\GenderConverter;
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
            if (\is_array($contactData)) {
                continue;
            }

            if (isset($normalizerMapping[$key])) {
                $contactsNormalized[$key] = $this->convert(
                    $contactData,
                    $normalizerMapping[$key],
                    $contact['IDPAYS'] ?? ''
                );

                continue;
            }

            $contactsNormalized[$key] = $contactData;
        }

        return $contactsNormalized;
    }

    private function convert(string $dataToConvert, string $normalizer, string $country)
    {
        switch ($normalizer) {
            case 'boolean':
                return BooleanConverter::convert($dataToConvert);
            case 'gender':
                return GenderConverter::convert($dataToConvert);
            case 'telephone':
                return TelephoneConverter::convert($dataToConvert, $country);
            default:
                return $dataToConvert;
        }
    }
}
