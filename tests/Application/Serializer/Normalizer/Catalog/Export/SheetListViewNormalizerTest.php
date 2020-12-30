<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Catalog\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\Serializer\Normalizer\Catalog\Export\SheetListViewNormalizer;
use Proximum\Vimeet\Application\Serializer\Normalizer\Catalog\Export\SheetViewNormalizer;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetListView;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetView;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SheetListViewNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $view1 = new SheetView(
            'Exposant',
            [
                'azerty1' => 'Proximum',
                'azerty2' => 'Paris',
                'azerty3' => 'France',
                'azerty4' => '321321',
            ],
            [
                'ytreza1' => 'Proximum Group',
                'ytreza2' => 'This is a description',
                'ytreza3' => 'Besoins',
            ],
            'President, Directeur'
        );
        $view2 = new SheetView(
            'Exposant',
            [
                'azerty1' => 'Aanera',
                'azerty2' => 'Berlin',
                'azerty3' => 'Germany',
                'azerty4' => '123123',
            ],
            [
                'ytreza1' => 'Aanera is a big company',
                'ytreza2' => 'This is a super different description',
                'ytreza3' => 'Needs',
            ],
            'Directeur commercial'
        );
        $view3 = new SheetView(
            'Visiteur',
            [
                'azerty1' => 'Apple',
                'azerty2' => 'Cuppertino',
                'azerty3' => 'USA',
                'azerty4' => '',
            ],
            [
                'ytreza1' => 'Apple Corp.',
                'ytreza2' => 'Sin autem ad adulescentiam perduxissent, dirimi tamen interdum contentione vel uxoriae condicionis vel commodi alicuius, quod idem adipisci uterque non posset. Quod si qui longius in amicitia provecti essent, tamen saepe labefactari, si in honoris contentionem incidissent; pestem enim nullam maiorem esse amicitiis quam in plerisque pecuniae cupiditatem, in optimis quibusque honoris certamen et gloriae; ex quo inimicitias maximas saepe inter amicissimos exstitisse.',
                'ytreza3' => 'No needs',
            ],
            'Chief Marketing Officer'
        );

        $sheetList = new SheetListView(
            [$view1, $view2, $view3],
            [
                'azerty1' => 'Titre',
                'azerty2' => 'Ville',
                'azerty3' => 'Pays',
                'azerty4' => 'TVA Intracommunautaire',
            ],
            [
                'ytreza1' => 'Titre de la fiche',
                'ytreza2' => 'Description',
                'ytreza3' => 'Besoins',
            ],
            'Fonctions des participants',
            'Type de participation',
            false
        );

        $normalizer = new SheetListViewNormalizer();
        $sheetNormalizer = new SheetViewNormalizer();
        $normalizer->setNormalizer($sheetNormalizer);
        $result = $normalizer->normalize($sheetList, 'csv', ['charset' => Charset::WINDOWS_1252, 'csv_delimiter' => ';']);

        $expected = [
            [
                'type'        => 'Type de participation',
                'azerty1'     => 'Titre',
                'azerty2'     => 'Ville',
                'azerty3'     => 'Pays',
                'azerty4'     => 'TVA Intracommunautaire',
                'participant' => 'Fonctions des participants',
                'ytreza1'     => 'Titre de la fiche',
                'ytreza2'     => 'Description',
                'ytreza3'     => 'Besoins',
            ],
            [
                'type'        => 'Exposant',
                'azerty1'     => 'Proximum',
                'azerty2'     => 'Paris',
                'azerty3'     => 'France',
                'azerty4'     => '321321',
                'participant' => 'President, Directeur',
                'ytreza1'     => 'Proximum Group',
                'ytreza2'     => 'This is a description',
                'ytreza3'     => 'Besoins',
            ],
            [
                'type'        => 'Exposant',
                'azerty1'     => 'Aanera',
                'azerty2'     => 'Berlin',
                'azerty3'     => 'Germany',
                'azerty4'     => '123123',
                'participant' => 'Directeur commercial',
                'ytreza1'     => 'Aanera is a big company',
                'ytreza2'     => 'This is a super different description',
                'ytreza3'     => 'Needs',
            ],
            [
                'type'        => 'Visiteur',
                'azerty1'     => 'Apple',
                'azerty2'     => 'Cuppertino',
                'azerty3'     => 'USA',
                'azerty4'     => '',
                'participant' => 'Chief Marketing Officer',
                'ytreza1'     => 'Apple Corp.',
                'ytreza2'     => 'Sin autem ad adulescentiam perduxissent, dirimi tamen interdum contentione vel uxoriae condicionis vel commodi alicuius, quod idem adipisci uterque non posset. Quod si qui longius in amicitia provecti essent, tamen saepe labefactari, si in honoris contentionem incidissent; pestem enim nullam maiorem esse amicitiis quam in plerisque pecuniae cupiditatem, in optimis quibusque honoris certamen et gloriae; ex quo inimicitias maximas saepe inter amicissimos exstitisse.',
                'ytreza3'     => 'No needs',
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testSerialize()
    {
        $view1 = new SheetView(
            'Exposant',
            [
                'azerty1' => 'Proximum',
                'azerty2' => 'Paris',
                'azerty3' => 'France',
                'azerty4' => '321321',
            ],
            [
                'ytreza1' => 'Proximum Group',
                'ytreza2' => 'This is a description',
                'ytreza3' => 'Besoins',
            ],
            'President, Directeur'
        );
        $view2 = new SheetView(
            'Exposant',
            [
                'azerty1' => 'Aanera',
                'azerty2' => 'Berlin',
                'azerty3' => 'Germany',
                'azerty4' => '123123',
            ],
            [
                'ytreza1' => 'Aanera is a big company',
                'ytreza2' => 'This is a super different description',
                'ytreza3' => 'Needs',
            ],
            'Directeur commercial'
        );
        $view3 = new SheetView(
            'Visiteur',
            [
                'azerty1' => 'Apple',
                'azerty2' => 'Cuppertino',
                'azerty3' => 'USA',
                'azerty4' => '',
            ],
            [
                'ytreza1' => 'Apple Corp.',
                'ytreza2' => 'Sin autem ad adulescentiam perduxissent, dirimi tamen interdum contentione vel uxoriae condicionis vel commodi alicuius, quod idem adipisci uterque non posset. Quod si qui longius in amicitia provecti essent, tamen saepe labefactari, si in honoris contentionem incidissent; pestem enim nullam maiorem esse amicitiis quam in plerisque pecuniae cupiditatem, in optimis quibusque honoris certamen et gloriae; ex quo inimicitias maximas saepe inter amicissimos exstitisse.',
                'ytreza3' => 'No needs',
            ],
            'Chief Marketing Officer'
        );

        $sheetList = new SheetListView(
            [$view1, $view2, $view3],
            [
                'azerty1' => 'Titre',
                'azerty2' => 'Ville',
                'azerty3' => 'Pays',
                'azerty4' => 'TVA Intracommunautaire',
            ],
            [
                'ytreza1' => 'Titre de la fiche',
                'ytreza2' => 'Description',
                'ytreza3' => 'Besoins',
            ],
            'Fonctions des participants',
            'Type de participation',
            false
        );

        $serializer = new Serializer(
            [
                new SheetListViewNormalizer(),
                new SheetViewNormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new CsvEncoder(),
            ]
        );

        $result = $serializer->serialize($sheetList, 'csv', ['charset' => Charset::WINDOWS_1252, 'csv_delimiter' => ';']);

        $expected = "type;azerty1;azerty2;azerty3;azerty4;participant;ytreza1;ytreza2;ytreza3\n\"Type de participation\";Titre;Ville;Pays;\"TVA Intracommunautaire\";\"Fonctions des participants\";\"Titre de la fiche\";Description;Besoins\nExposant;Proximum;Paris;France;321321;\"President, Directeur\";\"Proximum Group\";\"This is a description\";Besoins\nExposant;Aanera;Berlin;Germany;123123;\"Directeur commercial\";\"Aanera is a big company\";\"This is a super different description\";Needs\nVisiteur;Apple;Cuppertino;USA;;\"Chief Marketing Officer\";\"Apple Corp.\";\"Sin autem ad adulescentiam perduxissent, dirimi tamen interdum contentione vel uxoriae condicionis vel commodi alicuius, quod idem adipisci uterque non posset. Quod si qui longius in amicitia provecti essent, tamen saepe labefactari, si in honoris contentionem incidissent; pestem enim nullam maiorem esse amicitiis quam in plerisque pecuniae cupiditatem, in optimis quibusque honoris certamen et gloriae; ex quo inimicitias maximas saepe inter amicissimos exstitisse.\";\"No needs\"\n";

        $this->assertEquals($expected, $result);
    }
}
