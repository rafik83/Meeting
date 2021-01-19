<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Normalizer\Analytic\MeetingSolution\SheetSatisfactionViewNormalizer;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SheetSatisfactionViewNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $data = new SheetSatisfactionView(86, 'Test Title', 9, 'Fournisseur', 100);

        $serializer = new Serializer(
            [
                new SheetSatisfactionViewNormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new JsonEncoder(),
            ]
        );
        $result = $serializer->serialize($data, 'json');

        $expected = '{"sheetId":86,"sheetTitle":"Test Title","typeId":9,"typeTitle":"Fournisseur","satisfaction":100}';

        $this->assertEquals($expected, $result);
    }
}
