<?php

namespace Proximum\Vimeet\Application\Command\Sheet\SheetViewed;

use Proximum\Vimeet\Application\Adapter\ImpersonatingUserCheckerInterface;
use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;

class AddHandler
{
    /** @var SheetViewedRepositoryInterface */
    private $sheetViewedRepository;

    /** @var ImpersonatingUserCheckerInterface */
    private $impersonatingUserChecker;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * AddHandler constructor.
     *
     * @param SheetViewedRepositoryInterface    $sheetViewedRepository
     * @param ImpersonatingUserCheckerInterface $impersonatingUserChecker
     * @param \DateTimeInterface                $dateTime
     */
    public function __construct(
        SheetViewedRepositoryInterface $sheetViewedRepository,
        ImpersonatingUserCheckerInterface $impersonatingUserChecker,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetViewedRepository    = $sheetViewedRepository;
        $this->impersonatingUserChecker = $impersonatingUserChecker;
        $this->dateTime                 = $dateTime;
    }

    /**
     * Mark specified sheet as viewed by specified user
     *
     * @param Add $command
     */
    public function handle(Add $command)
    {
        if (!$this->impersonatingUserChecker->isImpersonated()
            && !$this->sheetViewedRepository->isSheetAlreadySeenByUser($command->user, $command->sheet)
        ) {
            $sheetViewed = new SheetViewed($command->sheet, $command->user, $this->dateTime);
            $this->sheetViewedRepository->add($sheetViewed);
        }
    }
}
