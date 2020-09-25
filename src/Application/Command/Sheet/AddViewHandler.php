<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\ImpersonatingUserCheckerInterface;
use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AddViewHandler implements Command
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;
    /**
     * @var ImpersonatingUserCheckerInterface
     */
    private $impersonatingUserChecker;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ImpersonatingUserCheckerInterface $impersonatingUserChecker
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->impersonatingUserChecker = $impersonatingUserChecker;
    }

    public function handle(AddView $addView): void
    {
        if ($this->impersonatingUserChecker->isImpersonated()) {
            return;
        }

        $isParticipantOfSeeingSheet = null !== $addView->sheet->hasUser($addView->user);

        if ($isParticipantOfSeeingSheet) {
            return;
        }

        $analytics = $addView->sheet->getAnalytics();
        $analytics->incrementViews($addView->user);

        $this->sheetRepository->set($addView->sheet);
    }
}
