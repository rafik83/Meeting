<?php

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TaggedInfoGuesser;

class VianeoSheetInfoGuesser
{
    /**
     * @var TaggedInfoGuesser
     */
    private $taggedInfoGuesser;

    /**
     * @param TaggedInfoGuesser $taggedInfoGuesser
     */
    public function __construct(TaggedInfoGuesser $taggedInfoGuesser)
    {
        $this->taggedInfoGuesser = $taggedInfoGuesser;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return array
     */
    public function handle(Sheet $sheet, string $locale): array
    {
        $infos    = [];
        $template = $sheet->getType()->getRegistrationTemplate();

        $tags = array_merge(
            [
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
            ],
            VianeoTemplateTag::getAllTags()
        );

        foreach ($tags as $tag) {
            $infos[$tag] = $this->taggedInfoGuesser->guessFirst(
                $template,
                $sheet->getRegistrationData(),
                $tag,
                $locale
            );
        }

        return $infos;
    }
}
