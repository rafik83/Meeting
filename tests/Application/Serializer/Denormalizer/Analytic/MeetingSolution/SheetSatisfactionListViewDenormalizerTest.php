<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Denormalizer\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution\SheetSatisfactionListViewDenormalizer;
use Proximum\Vimeet\Application\Serializer\Denormalizer\Analytic\MeetingSolution\SheetSatisfactionViewDenormalizer;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionListView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SheetSatisfactionListViewDenormalizerTest extends TestCase
{
    public function testDenormalize()
    {
        $data = json_decode("[{\"sheetId\":271,\"sheetTitle\":\"SATT GRAND EST\",\"typeId\":12,\"typeTitle\":\"Fournisseur\",\"satisfaction\":100},{\"sheetId\":319,\"sheetTitle\":\"EBAUCHES MICROMECANIQUE PRECITRAME\",\"typeId\":12,\"typeTitle\":\"Fournisseur\",\"satisfaction\":89},{\"sheetId\":339,\"sheetTitle\":\"FUNCOATS\",\"typeId\":14,\"typeTitle\":\"Start-up\",\"satisfaction\":48},{\"sheetId\":358,\"sheetTitle\":\"MULTIPRISE\",\"typeId\":13,\"typeTitle\":\"Donneur d'ordres\",\"satisfaction\":70},{\"sheetId\":427,\"sheetTitle\":\"MOULINAGE DU SOLIER\",\"typeId\":13,\"typeTitle\":\"Donneur d'ordres\",\"satisfaction\":100},{\"sheetId\":437,\"sheetTitle\":\"IPC INNOVATION PLASTURGIE COMPOSITES\",\"typeId\":12,\"typeTitle\":\"Fournisseur\",\"satisfaction\":87},{\"sheetId\":449,\"sheetTitle\":\"KERANOVA\",\"typeId\":13,\"typeTitle\":\"Donneur d'ordres\",\"satisfaction\":100}]", true);

        $serializer = new Serializer(
            [
                new SheetSatisfactionListViewDenormalizer(),
                new SheetSatisfactionViewDenormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );

        $result = $serializer->denormalize($data, SheetSatisfactionListView::class, 'json');

        $sheetSatisfaction1 = new SheetSatisfactionView(271, 'SATT GRAND EST', 12, 'Fournisseur', 100);
        $sheetSatisfaction2 = new SheetSatisfactionView(
            319,
            'EBAUCHES MICROMECANIQUE PRECITRAME',
            12,
            'Fournisseur',
            89
        );
        $sheetSatisfaction3 = new SheetSatisfactionView(339, 'FUNCOATS', 14, 'Start-up', 48);
        $sheetSatisfaction4 = new SheetSatisfactionView(358, 'MULTIPRISE', 13, 'Donneur d\'ordres', 70);
        $sheetSatisfaction5 = new SheetSatisfactionView(427, 'MOULINAGE DU SOLIER', 13, 'Donneur d\'ordres', 100);
        $sheetSatisfaction6 = new SheetSatisfactionView(
            437,
            'IPC INNOVATION PLASTURGIE COMPOSITES',
            12,
            'Fournisseur',
            87
        );
        $sheetSatisfaction7 = new SheetSatisfactionView(449, 'KERANOVA', 13, 'Donneur d\'ordres', 100);
        $expected           = new SheetSatisfactionListView();
        $expected->addSheetSatisfaction($sheetSatisfaction1);
        $expected->addSheetSatisfaction($sheetSatisfaction2);
        $expected->addSheetSatisfaction($sheetSatisfaction3);
        $expected->addSheetSatisfaction($sheetSatisfaction4);
        $expected->addSheetSatisfaction($sheetSatisfaction5);
        $expected->addSheetSatisfaction($sheetSatisfaction6);
        $expected->addSheetSatisfaction($sheetSatisfaction7);

        $this->assertEquals($expected, $result);
    }
}
