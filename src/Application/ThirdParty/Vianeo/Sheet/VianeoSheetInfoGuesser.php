<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
        $tags     = array_merge(Tag::getSheetTags(), VianeoTemplateTag::getAllTags());

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
