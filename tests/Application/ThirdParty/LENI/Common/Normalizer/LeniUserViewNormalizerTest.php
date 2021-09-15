<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\Normalizer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Normalizer\LeniUserViewNormalizer;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeaderView;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeniPlanningDayView;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeniPlanningView;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeniUserView;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;

class LeniUserViewNormalizerTest extends TestCase
{
    /** @var LeniUserViewNormalizer */
    private $normalizer;

    /** @var ObjectProphecy */
    private $extraData;

    public function setUp()
    {
        $this->extraData = $this->prophesize(ExtraData::class);
        $this->normalizer = new LeniUserViewNormalizer();
    }

    public function testNormalizeWithoutPreviousId()
    {
        $leniUserView = new LeniUserView(
            1337,
            true,
            'Star Fleet - Paris II Pantéon - Assas Sorbonne; Centre Thucydide pour le droit et les relations internationales',
            369,
            963,
            'James Tiberius Kirk',
            'man',
            'James Tiberius',
            'Kirk',
            'Captain',
            '+888999000',
            '+888999666',
            'US',
            'en',
            null,
            new LeniPlanningView(
                [
                    new LeniPlanningDayView('Planning day one'),
                    new LeniPlanningDayView('Planning day two'),
                ],
                'Unallocated: Klingon'
            ),
            null,
            true,
            971,
            [
                'ZL_ACTIVITE' => 'A1',
                'ZL_PROFIL' => 'VISITEUR',
            ]
        );

        $expectedLeniUserViewSerialized = [
            'Cab2' => '1337',
            'CleExterne' => 1337,
            'Societe' => 'Star Fleet - Paris II Pantéon - Assas Sorbonne; Centre Thucydide pour le droit et les relations inte',
            'Civilite' => 'M',
            'Prenom' => 'James Tiberius',
            'Nom' => 'Kirk',
            'Fonction' => 'Captain',
            'Email' => 'James Tiberius Kirk',
            'TelephoneMobile' => '+888999666',
            'TelephoneFixe' => '+888999000',
            'ZL_RDVNONORGANISES' => 'Unallocated: Klingon',
            'Pays' => 'US',
            'Langue' => 'en',
            'ZL_ACTIF' => 'ACTI',
            'ZL_ETATDEPAIEMENT' => 'PA',
            'ZL_IDPRODUITPARTICIPANT' => 971,
            'ZL_JOURNEE1' => 'Planning day one',
            'ZL_JOURNEE2' => 'Planning day two',
            'ZL_ACTIVITE' => 'A1',
            'ZL_PROFIL' => 'VISITEUR',
        ];

        $leniUserViewSerialized = $this->normalizer->normalize($leniUserView);

        $this->assertEquals($expectedLeniUserViewSerialized, $leniUserViewSerialized);
    }

    public function testNormalizeWithPreviousId()
    {
        $leaderView = new LeaderView(
            '123-321',
            'email@example.net',
            'George Samuel',
            'Kirk',
            'Star Fleet'
        );
        $leniUserView = new LeniUserView(
            1337,
            false,
            'Star Fleet',
            369,
            963,
            'James Tiberius Kirk',
            'man',
            'James Tiberius',
            'Kirk',
            'Captain',
            '+888999000',
            '+888999666',
            'US',
            'en',
            $leaderView,
            new LeniPlanningView(
                [
                    new LeniPlanningDayView('Planning day one'),
                    new LeniPlanningDayView('Planning day two'),
                ],
                'Unallocated: Klingon'
            ),
            '25b850a8-aed5-e711-80e0-0cc47a4c19cf',
            false,
            null,
            []
        );

        $expectedLeniUserViewSerialized = [
            'Cab2' => '1337',
            'CleExterne' => 1337,
            'Societe' => 'Star Fleet',
            'Civilite' => 'M',
            'Prenom' => 'James Tiberius',
            'Nom' => 'Kirk',
            'Fonction' => 'Captain',
            'Email' => 'James Tiberius Kirk',
            'TelephoneMobile' => '+888999666',
            'TelephoneFixe' => '+888999000',
            'ZL_RDVNONORGANISES' => 'Unallocated: Klingon',
            'Pays' => 'US',
            'Langue' => 'en',
            'ZL_ACTIF' => 'INAC',
            'ZL_ETATDEPAIEMENT' => 'PP',
            'ZL_JOURNEE1' => 'Planning day one',
            'ZL_JOURNEE2' => 'Planning day two',
            'ZL_LEADER_ID' => '123-321',
            'ZL_LEADER_SOCIETE' => 'Star Fleet',
            'ZL_LEADER_EMAIL' => 'email@example.net',
            'ZL_LEADER_NOM' => 'Kirk',
            'ZL_LEADER_PRENOM' => 'George Samuel',
            'Id' => '25b850a8-aed5-e711-80e0-0cc47a4c19cf',
        ];

        $leniUserViewSerialized = $this->normalizer->normalize($leniUserView);

        $this->assertEquals($expectedLeniUserViewSerialized, $leniUserViewSerialized);
    }
}
