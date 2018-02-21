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
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantPositionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class RawRegistrationToRegistrationViewConverter
{
    private const GENDER_MAPPING = [
        '1' => Gender::WOMAN, // Mademoiselle
        '21' => Gender::WOMAN, // Madame
        '22' => Gender::MAN, // Monsieur
    ];

    /**
     * @see registration wsdl: http://webservices.comexposium-admin.com/catalogue-ws-v2/inscriptionclientws.wsdl
     *
     * @param \stdClass $registration
     *
     * @return null|RegistrationView
     */
    public function convert(\stdClass $registration): ?RegistrationView
    {
        if (!isset($registration->reference, $registration->etatExposant)) {
            return null;
        }

        $participantView = $this->getParticipantView($registration);

        if (!$participantView instanceof ParticipantView) {
            return null;
        }

        return new RegistrationView(
            $registration->reference,
            $registration->raisonSociale ?? null,
            $registration->etatExposant,
            $registration->adresse1 ?? null,
            $registration->codePostal ?? null,
            $registration->ville ?? null,
            isset($registration->referencePays)
                ? $this->convertAlpha3ToAlpha2CodeCountry($registration->referencePays)
                : null,
            $registration->telephone ?? null,
            $registration->siteInternet ?? null,
            $participantView,
            $this->convertToArray($registration->referenceNomenclatureManifestation)
        );
    }

    /**
     * @param \stdClass $registration
     *
     * @return null|ParticipantView
     */
    private function getParticipantView(\stdClass $registration): ?ParticipantView
    {
        if (!isset($registration->responsableSalon, $registration->responsableSalon->email)) {
            return null;
        }

        $user = $registration->responsableSalon;

        return new ParticipantView(
            $this->getUserGender($user),
            $user->prenom ?? '',
            $user->nom ?? '',
            $user->email,
            $this->convertLocale($user->referenceLangueResponsableSalon),
            $user->telephone ?? null,
            $user->raisonSociale ?? null,
            $this->getParticipantPositionViews($user)
        );
    }

    /**
     * @param \stdClass $user
     *
     * @return ParticipantPositionView[]
     */
    private function getParticipantPositionViews(\stdClass $user): array
    {
        if (!isset($user->contactTitreTrad)) {
            return [];
        }

        $positions = $this->convertToArray($user->contactTitreTrad);

        return array_map(
            function (\stdClass $position) {
                return new ParticipantPositionView(
                    $position->traduction,
                    $this->convertLocale($position->referenceLangue)
                );
            },
            $positions
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

    /**
     * @param string $language
     *
     * @return string
     */
    private function convertLocale(string $language): string
    {
        return $language === 'FRA' ? 'fr' : 'en';
    }

    /**
     * @param \stdClass $user
     *
     * @return null|string
     */
    private function getUserGender(\stdClass $user): ?string
    {
        if (!isset($user->referenceCivilite)) {
            return null;
        }

        return self::GENDER_MAPPING[$user->referenceCivilite] ?? null;
    }
}
