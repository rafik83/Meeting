<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Planning\Formatter;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Service\MarkdownFormatter;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;

class UnallocatedFormatter
{
    const TRANSLATE_UNALLOCATED = 'planning.participant.unallocated_meetings';
    const TRANSLATION_DOMAIN    = 'messages';

    /**
     * @var TranslatorAdapter
     */
    private $translator;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @param TranslatorAdapter          $translator
     * @param RequestRepositoryInterface $requestRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     */
    public function __construct(
        TranslatorAdapter $translator,
        RequestRepositoryInterface $requestRepository,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->translator        = $translator;
        $this->requestRepository = $requestRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
    }

    /**
     * Return a list of all the meeting request of a sheet not converted to meeting
     *
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return string
     */
    public function format(Sheet $sheet, $locale)
    {
        $requests = $this->requestRepository->getUnassignedRequestsBySheetAndEvent($sheet, Request::STATE_APPROVED);

        if (count($requests) === 0) {
            return '';
        }

        $formatted = MarkdownFormatter::newLine(
            $this->translator->trans(self::TRANSLATE_UNALLOCATED, [], self::TRANSLATION_DOMAIN, $locale)
        );

        $formatted .= implode(', ', array_map(function (Request $request) use ($sheet) {
            return $this->sheetInfoGuesser->guessSheetTitle($request->getSheetMet($sheet));
        }, $requests));

        return $formatted;
    }
}
