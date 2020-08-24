<?php


namespace Proximum\Vimeet\Tests\Application\ThirdParty\TechEvent\Webservice\Normalizer;

use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Normalizer\ContactNormalizer;
use PHPUnit\Framework\TestCase;

class ContactNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $normalizerMapping = [
            "TEL" => "telephone",
            "IDCIVILITE" => "gender",
            "RDV_B2B" => "boolean",
            "IDPAYS" => "country"
        ];

        $contact = [
            "IDCONTACT" => "113893672",
            "SOCIETE" => "TPM",
            "IDCIVILITE" => "M  ",
            "NOM" => "HAMLAT",
            "PRENOM" => "MOHAMED",
            "TEL" => "0666778877",
            "ADRESSE1" => "27 FERMÉ ABDELKADER ALLAOUA KOUBA",
            "CODEPOSTAL" => "16006",
            "VILLE" => "FRANCE",
            "IDPAYS" => "FR",
            "EMAIL" => "HAMLETLEE16@HOTMAIL.COM",
            "Secteur_activité" => "A99  ",
            "Préciser_Activite_Autre" => "TRANSPORT ET LOGISTIQUE",
            "Typologie_société" => "TP99 ",
            "Préciser_Typologie" => "PME",
            "Nombre_Personnes" => "T1   ",
            "Votre_Fonction" => "F02  ",
            "Nature_de_votre_société_organisation" => "D07  ",
            "Centres_intérêt" => "I01;I02;I03;I04;I05;I07",
            "Conference" => "Y",
            "RDV_B2B" => "0",
        ];

        $expectedResult = [
            "IDCONTACT" => "113893672",
            "SOCIETE" => "TPM",
            "IDCIVILITE" => "man",
            "NOM" => "HAMLAT",
            "PRENOM" => "MOHAMED",
            "TEL" => "+33666778877",
            "ADRESSE1" => "27 FERMÉ ABDELKADER ALLAOUA KOUBA",
            "CODEPOSTAL" => "16006",
            "VILLE" => "FRANCE",
            "IDPAYS" => "FR",
            "EMAIL" => "HAMLETLEE16@HOTMAIL.COM",
            "Secteur_activité" => "A99  ",
            "Préciser_Activite_Autre" => "TRANSPORT ET LOGISTIQUE",
            "Typologie_société" => "TP99 ",
            "Préciser_Typologie" => "PME",
            "Nombre_Personnes" => "T1   ",
            "Votre_Fonction" => "F02  ",
            "Nature_de_votre_société_organisation" => "D07  ",
            "Centres_intérêt" => "I01;I02;I03;I04;I05;I07",
            "Conference" => "Y",
            "RDV_B2B" => false
        ];

        $normalizer = new ContactNormalizer();
        $result = $normalizer->normalize($contact, $normalizerMapping);

        $this->assertSame($result, $expectedResult);
    }
}
