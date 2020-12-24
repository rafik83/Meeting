<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Catalog\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\Serializer\Normalizer\Catalog\Export\SheetViewNormalizer;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetView;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SheetViewNormalizerTest extends TestCase
{
    public function testNormalize()
    {
        $view = new SheetView(
            'Exposant',
            [
                'azerty1' => 'Title',
                'azerty2' => 'Paris',
                'azerty3' => 'France',
            ],
            [
                'ytreza1' => 'Aanera',
                'ytreza2' => 'This is a description',
                'ytreza3' => 'Needs',
            ],
            'President, Directeur'
        );

        $normalizer = new SheetViewNormalizer();

        $result =$normalizer->normalize($view, 'csv', ['charset' => Charset::WINDOWS_1252, 'csv_delimiters' => ';']);

        $expected = [
            'type'        => 'Exposant',
            'azerty1'     => 'Title',
            'azerty2'     => 'Paris',
            'azerty3'     => 'France',
            'participant' => 'President, Directeur',
            'ytreza1'     => 'Aanera',
            'ytreza2'     => 'This is a description',
            'ytreza3'     => 'Needs',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testSerialize()
    {
        $view = new SheetView(
            'Exposant',
            [
                'azerty1' => 'Title',
                'azerty2' => 'Paris',
                'azerty3' => 'France',
            ],
            [
                'ytreza1' => 'Aanera',
                'ytreza2' => 'This is a description',
                'ytreza3' => 'Besoins',
            ],
            'President, Directeur'
        );

        $serializer = new Serializer(
            [
                new SheetViewNormalizer(),
                new ObjectNormalizer(),
            ],
            [
                new CsvEncoder(),
            ]
        );

        $result = $serializer->serialize($view, 'csv', ['charset' => Charset::WINDOWS_1252, 'csv_delimiter' => ';']);

        $expected = "type;azerty1;azerty2;azerty3;participant;ytreza1;ytreza2;ytreza3\nExposant;Title;Paris;France;\"President, Directeur\";Aanera;\"This is a description\";Besoins\n";

        $this->assertEquals($expected, $result);
    }
}
