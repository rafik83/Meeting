<?php

namespace Proximum\Vimeet\Application\Components\Planning\Formatter;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\MarkdownFormatter;

class UnallocatedFormatter
{
    const TRANSLATE_UNALLOCATED = 'planning.participant.unallocated_meetings';
    const TRANSLATION_DOMAIN    = 'messages';

    /** @var TranslatorInterface */
    private $translator;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param TranslatorInterface        $translator
     * @param RequestRepositoryInterface $requestRepository
     * @param SheetRepositoryInterface   $sheetRepository
     */
    public function __construct(
        TranslatorInterface $translator,
        RequestRepositoryInterface $requestRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->translator        = $translator;
        $this->requestRepository = $requestRepository;
        $this->sheetRepository   = $sheetRepository;
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

        if (0 === count($requests)) {
            return '';
        }

        $formatted = MarkdownFormatter::newLine(
            $this->translator->trans(self::TRANSLATE_UNALLOCATED, [], self::TRANSLATION_DOMAIN, $locale)
        );

        $formatted .= implode(', ', array_map(function (Request $request) use ($sheet) {
            return $request->getSheetMet($sheet)->getTitle();
        }, $requests));

        return $formatted;
    }

    /**
     * Return a list of all the meeting request of a user sheets not converted to meeting
     *
     * @param Event  $event
     * @param User   $user
     * @param string $locale
     * @param bool   $isUserMultipleSheets
     *
     * @return string
     */
    public function formatForUser(Event $event, User $user, $locale, $isUserMultipleSheets = false)
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);
        $requests = $this->requestRepository->getUnallocatedRequestForSheets($sheets);

        if (0 === count($requests)) {
            return '';
        }

        $formatted = MarkdownFormatter::newLine(
            $this->translator->trans(self::TRANSLATE_UNALLOCATED, [], self::TRANSLATION_DOMAIN, $locale)
        );

        $formatted .= implode(
            ', ',
            array_map(function (Request $request) use ($user, $isUserMultipleSheets) {
                $userSheet       = $request->getSheetOfUser($user);
                $unallocatedText = $request->getSheetMet($userSheet)->getTitle();

                if ($isUserMultipleSheets) {
                    $unallocatedText .= ' (' . $userSheet->getTitle() . ')';
                }

                return $unallocatedText;
            }, $requests)
        );

        return $formatted;
    }
}
