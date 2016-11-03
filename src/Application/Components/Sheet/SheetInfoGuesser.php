<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TaggedInfoGuesser;

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
     * @param TaggedInfoGuesser      $taggedInfoGuesser
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(
        TaggedInfoGuesser $taggedInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->taggedInfoGuesser      = $taggedInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return string
     */
    public function guessOwnerInfo(Sheet $sheet, $locale)
    {
        try {
            $participant = $sheet->getParticipantOwner();

            if (null !== $participant) {
                return $this->participantInfoGuesser->guessParticipantCompleteName($sheet->getParticipantOwner(), $locale);
            } else {
                return sprintf(
                    '%s %s',
                    $sheet->getOwner()->getAccount()->getFirstName(),
                    $sheet->getOwner()->getAccount()->getLastName()
                );
            }
        } catch (\RuntimeException $exception) {
            return '';
        }
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return string
     *
     * @deprecated Use instead SheetInfoGuesser::guessSheetTitle()
     */
    public function guessSheetName(Sheet $sheet, $locale)
    {
        return $this->guessSheetTitle($sheet, $locale);
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return string
     */
    public function guessSheetTitle(Sheet $sheet, $locale)
    {
        $template = $sheet->getType()->getRegistrationTemplate();

        if (null === $template) {
            return sprintf('#%s', $sheet->getId());
        }

        $data = $sheet->getRegistrationData();
        $info = $this->taggedInfoGuesser->guessFirst($template, $data, Tag::SHEET_TITLE, $locale);

        if (!empty($info)) {
            return $info;
        }

        $info = $this->taggedInfoGuesser->guessFirst($template, $data, Tag::SHEET_ORGANIZATION, $locale);

        if (!empty($info)) {
            return $info;
        }

        $owner = $this->guessOwnerInfo($sheet, $locale);

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
     *
     * @deprecated Must be rewrited in order to get Sheet productsSelectionTemplate and corresponding data
     */
    public function guessSheetPackage(Sheet $sheet, $locale)
    {
        return '';
    }
}
