<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantPositionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationDescriptionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;

class RawRegistrationToRegistrationViewConverter extends ComexposiumConverter
{
    private const STATUS_WHITE_LIST = ['VALIDE', 'INSTANCE'];

    private const REGISTRATION_DESCRIPTION_FIELD = 'DESCRIPTION';

    private const GENDER_MAPPING = [
        '1' => Gender::WOMAN, // Mademoiselle
        '21' => Gender::WOMAN, // Madame
        '22' => Gender::MAN, // Monsieur
    ];

    /**
     * @see registration wsdl: http://webservices.comexposium-admin.com/catalogue-ws-v2/inscriptionclientws.wsdl
     *
     * @param \stdClass        $registration
     * @param NomenclatureView $nomenclatureView
     *
     * @return null|RegistrationView
     */
    public function convert(\stdClass $registration, NomenclatureView $nomenclatureView): ?RegistrationView
    {
        if (!isset($registration->reference, $registration->etatExposant)) {
            return null;
        }

        if (!$this->isRegistrationHasAWhiteListedStatus($registration->etatExposant)) {
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
            isset($registration->referenceNomenclatureManifestation)
                ? $this->getNomenclatureItemViews($registration->referenceNomenclatureManifestation, $nomenclatureView)
                : [],
            isset($registration->inscriptionTrad)
                ? $this->getRegistrationDescriptionViews($registration->inscriptionTrad)
                : []
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

    /**
     * @param string $status
     *
     * @return bool
     */
    private function isRegistrationHasAWhiteListedStatus(string $status): bool
    {
        return \in_array($status, self::STATUS_WHITE_LIST, true);
    }

    /**
     * @param array|\stdClass  $nomenclatureReferences
     * @param NomenclatureView $nomenclatureView
     *
     * @return array
     */
    private function getNomenclatureItemViews($nomenclatureReferences, NomenclatureView $nomenclatureView): array
    {
        $nomenclatureItemViews = [];
        $nomenclatureReferences = $this->convertToArray($nomenclatureReferences);

        foreach ($nomenclatureReferences as $nomenclatureReference) {
            $nomenclatureItemView = $nomenclatureView->getNomenclatureItemViewByReference($nomenclatureReference);

            if ($nomenclatureItemView instanceof NomenclatureItemView) {
                $nomenclatureItemViews[] = $nomenclatureItemView;
            }
        }

        return $nomenclatureItemViews;
    }

    /**
     * @param \stdClass|\stdClass[] $registrationTranslatedData
     *
     * @return RegistrationDescriptionView[]
     */
    private function getRegistrationDescriptionViews($registrationTranslatedData): array
    {
        $registrationTranslatedData = $this->convertToArray($registrationTranslatedData);
        $registrationDescriptionViews = [];

        foreach ($registrationTranslatedData as $data) {
            if (isset($data->inscriptionChamp, $data->referenceLangue, $data->traduction)
                && self::REGISTRATION_DESCRIPTION_FIELD === $data->inscriptionChamp
            ) {
                $registrationDescriptionViews[] = new RegistrationDescriptionView(
                    $data->traduction,
                    $this->convertLocale($data->referenceLangue)
                );
            }
        }

        return $registrationDescriptionViews;
    }
}
