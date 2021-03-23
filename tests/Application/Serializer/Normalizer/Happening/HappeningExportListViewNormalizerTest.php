<?php


namespace Proximum\Vimeet\Tests\Application\Serializer\Normalizer\Happening;


use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\Serializer\Normalizer\Happening\HappeningExportListViewNormalizer;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningExportListView;
use Proximum\Vimeet\Application\View\Happening\Admin\HappeningExportView;
use Proximum\Vimeet\Application\View\Happening\Admin\SpeakerExportView;
use Proximum\Vimeet\Application\View\Happening\Admin\SpeakersExportListView;

class HappeningExportListViewNormalizerTest extends TestCase
{
    public function testSupportsNormalization(): void
    {
        $data = new HappeningExportListView([]);
        $format = 'csv';
        $normalizer = new HappeningExportListViewNormalizer();
        $result = $normalizer->supportsNormalization($data, $format);
        self::assertTrue($result);
    }

    public function testNoSupportsNormalization(): void
    {
        $data = new HappeningExportListView([]);
        $format = 'json';
        $normalizer = new HappeningExportListViewNormalizer();
        $result = $normalizer->supportsNormalization($data, $format);
        self::assertFalse($result);
    }

    public function testNormalize(): void
    {
        $expectedResult = [
            [
                'title' => 'Carnauto : une action',
                'description' => 'description',
                'category' => 'cat',
                'begin' => '28-04-2020 10:20',
                'end' => '28-04-2020 11:00',
                'participant scanned' => 64,
                'number of grades' => 0,
                'average grades' => 0.0,
                'speaker name 0' => 'Dupont',
                'speaker position 0' => 'Web',
                'speaker society 0' => 'Google',
                'speaker avatar url 0' => 'http://avatar',
                'speaker logo url 0' => 'http://logo',
            ]
        ];

        $normalizer = new HappeningExportListViewNormalizer();
        $speakerList = new SpeakersExportListView([new SpeakerExportView('Dupont', 'Web', 'Google', 'http://logo', 'http://avatar')]);
        $object = new HappeningExportListView([new HappeningExportView(
            'Carnauto : une action',
            'description',
            'cat',
            '28-04-2020 10:20',
            '28-04-2020 11:00',
            $speakerList,
            64,
            0,
            0.0
        )]);

        $result = $normalizer->normalize($object, 'csv', ['charset' => Charset::WINDOWS_1252, 'csv_delimiter' => ';']);
        self::assertEquals($result, $expectedResult);
    }
}
