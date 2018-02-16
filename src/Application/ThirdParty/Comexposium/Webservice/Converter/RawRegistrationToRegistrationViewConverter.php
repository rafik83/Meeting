<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter;

use League\ISO3166\ISO3166;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;

class RawRegistrationToRegistrationViewConverter
{
    /**
     * @see registration wsdl: http://webservices.comexposium-admin.com/catalogue-ws-v2/inscriptionclientws.wsdl
     *
     * @param \stdClass $registration
     *
     * @return RegistrationView
     */
    public function convert(\stdClass $registration): RegistrationView
    {
        return new RegistrationView(
            $registration->reference,
            $registration->raisonSociale,
            $registration->etatExposant,
            $registration->adresse1,
            $registration->codePostal,
            $registration->ville,
            $this->convertAlpha3ToAlpha2CodeCountry($registration->referencePays),
            $registration->telephone,
            $registration->siteInternet,
            new ParticipantView(),
            $this->convertToArray($registration->referenceNomenclatureManifestation)
        );
    }

    /**
     * @param string $countryAlpha3Code
     *
     * @return null|string
     */
    private function convertAlpha3ToAlpha2CodeCountry(string $countryAlpha3Code): ?string
    {
        try {
            $country = (new ISO3166)->alpha3($countryAlpha3Code);
            return $country[ISO3166::KEY_ALPHA2];
        } catch (\Exception $exception) {}

        return null;
    }

    /**
     * @param array|\stdClass $data
     *
     * @return array
     */
    private function convertToArray($data): array
    {
        return \is_array($data) ? $data : [$data];
    }
}
