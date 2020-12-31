<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter;

use League\ISO3166\ISO3166;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\View\UserInformationView;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class RawUserDataToUserInformationViewConverter
{
    private const GENDER_MAPPING = ['0' => Gender::MAN, '1' => Gender::MAN];

    /**
     * @param string $email
     * @param string $locale
     * @param array  $data
     *
     * @return UserInformationView
     */
    public function convert(string $email, string $locale, array $data): UserInformationView
    {
        return new UserInformationView(
            $email,
            isset($data['civility']) ? $this->convertCivility($data['civility']) : null,
            $data['firstname'] ?? null,
            $data['lastname'] ?? null,
            isset($data['mobilephone:indicatif'], $data['mobilephone:content'])
                ? $this->convertPhone($data['mobilephone:indicatif'], $data['mobilephone:content'])
                : null,
            isset($data['country']) ? $this->convertAlpha3ToAlpha2CodeCountry($data['country']) : null,
            $locale
        );
    }

    /**
     * @param string $civilityCode
     *
     * @return null|string
     */
    private function convertCivility(string $civilityCode): ?string
    {
        if (isset(self::GENDER_MAPPING[$civilityCode])) {
            return self::GENDER_MAPPING[$civilityCode];
        }

        return null;
    }

    /**
     * @param string $countryAlpha3Code
     *
     * @return null|string
     */
    private function convertAlpha3ToAlpha2CodeCountry(string $countryAlpha3Code): ?string
    {
        try {
            $country = (new ISO3166())->alpha3($countryAlpha3Code);

            return $country[ISO3166::KEY_ALPHA2];
        } catch (\Exception $exception) {
        }

        return null;
    }

    /**
     * @param string $prefix
     * @param string $phone
     *
     * @return string
     */
    private function convertPhone(string $prefix, string $phone): string
    {
        return sprintf('%s%s', $prefix, $phone);
    }
}
