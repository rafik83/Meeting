<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Vianeo\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet\VianeoSheetInfoGuesser;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet\VianeoTemplateTag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\TaggedInfoGuesser;

class VianeoSheetInfoGuesserTest extends TestCase
{
    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);

        $sheet->getType()->willReturn($type->reveal());
        $sheet->getRegistrationData()->willReturn(['some-template-data']);
        $type->getRegistrationTemplate()->willReturn($registrationTemplate->reveal());

        $taggedInfoGuesser = $this->prophesize(TaggedInfoGuesser::class);

        $allTags = [
            Tag::SHEET_ORGANIZATION,
            Tag::SHEET_TITLE,
            Tag::SHEET_ORGANIZATION_CATEGORY,
            Tag::SHEET_ORGANIZATION_TURNOVER,
            Tag::SHEET_ORGANIZATION_STAFF,
            Tag::SHEET_ADDRESS,
            Tag::SHEET_ZIPCODE,
            Tag::SHEET_CITY,
            Tag::SHEET_COUNTRY,
            Tag::SHEET_WEBSITE,
            Tag::SHEET_PHONE,
            VianeoTemplateTag::VIANEO_REGISTRATION,
            VianeoTemplateTag::VIANEO_CATEGORY,
            VianeoTemplateTag::VIANEO_PROJECT_SUMMARY,
        ];

        foreach ($allTags as $tag) {
            $taggedInfoGuesser
                ->guessFirst(
                    $registrationTemplate->reveal(),
                    ['some-template-data'],
                    $tag,
                    'fr'
                )
                ->shouldBeCalled()
                ->willReturn($tag . '_value')
            ;
        }

        $vianeoSheetInfoGuesser = new VianeoSheetInfoGuesser($taggedInfoGuesser->reveal());
        $result = $vianeoSheetInfoGuesser->handle($sheet->reveal(), 'fr');

        $expected = [
            'sheet_organization'          => 'sheet_organization_value',
            'sheet_title'                 => 'sheet_title_value',
            'sheet_organization_category' => 'sheet_organization_category_value',
            'sheet_organization_turnover' => 'sheet_organization_turnover_value',
            'sheet_organization_staff'    => 'sheet_organization_staff_value',
            'sheet_address'               => 'sheet_address_value',
            'sheet_zipcode'               => 'sheet_zipcode_value',
            'sheet_city'                  => 'sheet_city_value',
            'sheet_country'               => 'sheet_country_value',
            'sheet_website'               => 'sheet_website_value',
            'sheet_phone'                 => 'sheet_phone_value',
            'vianeo_registration'         => 'vianeo_registration_value',
            'vianeo_category'             => 'vianeo_category_value',
            'vianeo_project_summary'      => 'vianeo_project_summary_value',
        ];

        static::assertEquals($expected, $result);
    }
}
