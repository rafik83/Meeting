<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\LocaleConverter;

class LocaleConverterTest extends TestCase
{
    /**
     * @dataProvider provider
     *
     * @param string $locale
     * @param string $expected
     */
    public function testFormatLocale(string $locale, string $expected)
    {
        $converter = new LocaleConverter();
        $result = $converter->formatLocale($locale);

        $this->assertEquals($expected, $result);
    }

    public function provider()
    {
        return [
            ['fr', 'fre-FR'],
            ['en', 'eng-GB'],
            ['de', 'eng-GB'],
        ];
    }
}
