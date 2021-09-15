<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\ImpersonatingUserCheckerInterface;
use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AddClickHandler implements Command
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

    public function handle(AddClick $addClick): void
    {
        if ($this->impersonatingUserChecker->isImpersonated()) {
            return;
        }

        if ($addClick->sheet->hasUser($addClick->user)) {
            return;
        }

        $analytics = $addClick->sheet->getAnalytics();
        $analytics->incrementClicks($addClick->user, $addClick->objectId, $addClick->index);

        $this->sheetRepository->set($addClick->sheet);
    }
}
