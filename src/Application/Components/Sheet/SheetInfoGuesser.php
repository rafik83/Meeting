<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Application\Components\Template\TaggedInfoGuesser;

class SheetInfoGuesser
{
    /**
     * @var TaggedInfoGuesser
     */
    private $taggedInfoGuesser;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * SheetInfoGuesser constructor.
     *
     * @param TaggedInfoGuesser      $taggedInfoGuesser
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(TaggedInfoGuesser $taggedInfoGuesser, ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->taggedInfoGuesser      = $taggedInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function guessOwnerInfo(Sheet $sheet)
    {
        try {
            return $this->participantInfoGuesser->guessParticipantInfo($sheet->getOwner());
        } catch (\RuntimeException $exception) {
            return '';
        }
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function guessSheetInfo(Sheet $sheet)
    {
        $template = $sheet->getTypeSheetTemplate();
        $data     = $sheet->getData();
        $info     = $this->taggedInfoGuesser->guess($template, $data, Tag::SHEET_ORGANIZATION);

        if (!empty($info)) {
            return reset($info);
        }

        $owner = $this->guessOwnerInfo($sheet);

        if (!empty($owner)) {
            return $owner;
        }

        return sprintf('#%s', $sheet->getId());
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return string
     */
    public function guessSheetPackage(Sheet $sheet, $locale)
    {
        $template = $sheet->getTypePackageTemplate();
        $data     = $sheet->getPackageData();

        return $this->taggedInfoGuesser->guessFirst($template, $data, Tag::SHEET_PACKAGE, $locale);
    }
}
