<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter\RawRegistrationToRegistrationViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantPositionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationDescriptionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;

class RawRegistrationToRegistrationViewConverterTest extends TestCase
{
    public function testConvert()
    {
        $rawRegistration = new \stdClass();

        $rawRegistration->reference = '5556666';
        $rawRegistration->referenceExposant = '98798789';
        $rawRegistration->referenceClient = '98080989089';
        $rawRegistration->raisonSociale = 'Nintendo';
        $rawRegistration->etatExposant = 'VALIDE';
        $rawRegistration->etatInscription = 'MODIFICATION';
        $rawRegistration->adresse1 = '61 rue de l\'Odyssée';
        $rawRegistration->adresse2 = 'BP7777';
        $rawRegistration->codePostal = '75008';
        $rawRegistration->ville = 'Paris';
        $rawRegistration->referencePays = 'FRA';
        $rawRegistration->referencePaysAffilie = 'FRA';
        $rawRegistration->telephone = '33 (0)1 40 69 80 00';
        $rawRegistration->email = 'contact@nintendo.com';
        $rawRegistration->siteInternet = 'https://www.nintendo.com';
        $rawRegistration->tvaIntracommunautaire = 'FR 7868787687';
        $rawRegistration->referenceNomenclatureManifestation = ['666', '777', '88898'];
        $rawRegistration->typeExposant = 'DIRECT';
        $rawRegistration->referencePartenaire = ['2270402', '2273710'];
        $rawRegistration->dateMiseAJour = '2018-01-19T01:45:39.569+01:00';

        $responsableSalon = new \stdClass();
        $responsableSalon->referenceCivilite = '22';
        $responsableSalon->nom = 'Kitano';
        $responsableSalon->prenom = 'Takashi';
        $responsableSalon->email = 'takashi.kitano@nintendo.com';
        $responsableSalon->raisonSociale = 'Nintendo Europe';
        $responsableSalon->referenceLangueResponsableSalon = 'FRA';

        $contactTitreTrad1 = new \stdClass();
        $contactTitreTrad1->referenceLangue = 'FRA';
        $contactTitreTrad1->traduction = 'Directeur Export';

        $contactTitreTrad2 = new \stdClass();
        $contactTitreTrad2->referenceLangue = 'GBR';
        $contactTitreTrad2->traduction = 'Export Director';

        $responsableSalon->contactTitreTrad = [$contactTitreTrad1, $contactTitreTrad2];

        $rawRegistration->responsableSalon = $responsableSalon;

        $descriptionTrad1 = new \stdClass();
        $descriptionTrad1->referenceLangue = 'GBR';
        $descriptionTrad1->traduction = 'Scrap iron recycling machines';
        $descriptionTrad1->inscriptionChamp = 'DESCRIPTION';

        $descriptionTrad2 = new \stdClass();
        $descriptionTrad2->referenceLangue = 'FRA';
        $descriptionTrad2->traduction = 'Whatever description';
        $descriptionTrad2->inscriptionChamp = 'WHATEVER-ELSE';

        $rawRegistration->inscriptionTrad = [$descriptionTrad1, $descriptionTrad2];

        $nomenclatureView = new NomenclatureView([
            '666' => new NomenclatureItemView('666', null, []),
            '88898' => new NomenclatureItemView('88898', null, []),
        ]);

        $rawRegistrationToRegistrationViewConverter = new RawRegistrationToRegistrationViewConverter();
        $result = $rawRegistrationToRegistrationViewConverter->convert($rawRegistration, $nomenclatureView);

        $expectedResult = new RegistrationView(
            '5556666',
            'Nintendo',
            'VALIDE',
            '61 rue de l\'Odyssée',
            '75008',
            'Paris',
            'FR',
            '33 (0)1 40 69 80 00',
            'https://www.nintendo.com',
            new ParticipantView(
                'man',
                'Takashi',
                'Kitano',
                'takashi.kitano@nintendo.com',
                'fr',
                null,
                'Nintendo Europe',
                [
                    new ParticipantPositionView('Directeur Export', 'fr'),
                    new ParticipantPositionView('Export Director', 'en'),
                ]
            ),
            [
                new NomenclatureItemView('666', null, []),
                new NomenclatureItemView('88898', null, []),
            ],
            [
                new RegistrationDescriptionView('Scrap iron recycling machines', 'en'),
            ]
        );

        $this->assertEquals($expectedResult, $result);
    }
}
