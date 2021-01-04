<?php

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class TipTranslationViewByUserQueryHandler
{
    /** @var TipTranslationViewQueryHandler */
    private $tipTranslationViewQueryHandler;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param TipTranslationViewQueryHandler $tipTranslationViewQueryHandler
     * @param SheetRepositoryInterface       $sheetRepository
     */
    public function __construct(
        TipTranslationViewQueryHandler $tipTranslationViewQueryHandler,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->tipTranslationViewQueryHandler = $tipTranslationViewQueryHandler;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param TipTranslationViewByUserQuery $tipTranslationViewByUserQuery
     *
     * @throws SheetNotFoundException
     *
     * @return TipTranslationView[]
     */
    public function handle(TipTranslationViewByUserQuery $tipTranslationViewByUserQuery)
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent(
            $tipTranslationViewByUserQuery->user,
            $tipTranslationViewByUserQuery->event
        );

        $sheet = reset($sheets);

        if (false === $sheet) {
            throw new SheetNotFoundException('No sheet found for the user in the event');
        }

        return $this->tipTranslationViewQueryHandler->handle(
            new TipTranslationViewQuery(
                $sheet,
                $tipTranslationViewByUserQuery->user,
                $tipTranslationViewByUserQuery->context,
                $tipTranslationViewByUserQuery->locale
            )
        );
    }
}
