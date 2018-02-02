<?php
/**
 * Created by PhpStorm.
 * User: richard
 * Date: 02/02/2018
 * Time: 15:39
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Normalizer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\LENI\Normalizer\LeniUserViewNormalizer;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningDayView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniUserView;
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
            new LeniPlanningView(
                [
                    new LeniPlanningDayView('Planning day one'),
                    new LeniPlanningDayView('Planning day two'),
                ],
                'Unallocated: Klingon'
            ),
            null
        );

        $expectedLeniUserViewSerialized = [
            'Cab2' => '1337',
            'CleExterne' => 1337,
            'Societe' => 'Star Fleet',
            'CategorieIndividuEvt' => '963',
            'ZL_SOUSCATEGORIE' => '369',
            'Civilite' => 'M',
            'Prenom' => 'James Tiberius',
            'Nom' => 'Kirk',
            'Fonction' => 'Captain',
            'Email' => 'James Tiberius Kirk',
            'TelephoneMobile' => '+888999666',
            'TelephoneFixe' => '+888999000',
            'ZL_RDVNONORGANISES' => 'Unallocated: Klingon',
            'Pays' => 'US',
            'Inscrit' => 'Inscrit',
            'Langue' => 'en',
            'ZL_JOURNEE1' => 'Planning day one',
            'ZL_JOURNEE2' => 'Planning day two',
        ];

        $leniUserViewSerialized = $this->normalizer->normalize($leniUserView);

        $this->assertEquals($expectedLeniUserViewSerialized, $leniUserViewSerialized);
    }

    public function testNormalizeWithPreviousId()
    {
        $leniUserView = new LeniUserView(
            1337,
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
            new LeniPlanningView(
                [
                    new LeniPlanningDayView('Planning day one'),
                    new LeniPlanningDayView('Planning day two'),
                ],
                'Unallocated: Klingon'
            ),
            '25b850a8-aed5-e711-80e0-0cc47a4c19cf'
        );

        $expectedLeniUserViewSerialized = [
            'Cab2' => '1337',
            'CleExterne' => 1337,
            'Societe' => 'Star Fleet',
            'CategorieIndividuEvt' => '963',
            'ZL_SOUSCATEGORIE' => '369',
            'Civilite' => 'M',
            'Prenom' => 'James Tiberius',
            'Nom' => 'Kirk',
            'Fonction' => 'Captain',
            'Email' => 'James Tiberius Kirk',
            'TelephoneMobile' => '+888999666',
            'TelephoneFixe' => '+888999000',
            'ZL_RDVNONORGANISES' => 'Unallocated: Klingon',
            'Pays' => 'US',
            'Inscrit' => 'Inscrit',
            'Langue' => 'en',
            'ZL_JOURNEE1' => 'Planning day one',
            'ZL_JOURNEE2' => 'Planning day two',
            'Id' => '25b850a8-aed5-e711-80e0-0cc47a4c19cf',
        ];

        $leniUserViewSerialized = $this->normalizer->normalize($leniUserView);

        $this->assertEquals($expectedLeniUserViewSerialized, $leniUserViewSerialized);
    }
}
