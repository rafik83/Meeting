<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export;

use Proximum\Vimeet\Application\View\Rooming\ExportList\UserSheetView;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class UserViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var Merger */
    private $merger;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        Merger $merger
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->merger = $merger;
    }

    public function handle(UserViewQuery $query): UserSheetView
    {
        $comment = '';
        $tasting = '';
        $extraDataComment = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            Type::ROOMING_COMMENT,
            $query->user
        );

        if ($extraDataComment instanceof ExtraData) {
            $comment = $extraDataComment->getValue();
        }

        $extraDataTasting = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $query->event,
            Type::ROOMING_TASTING,
            $query->user
        );

        if ($extraDataTasting instanceof ExtraData) {
            $tasting = $extraDataTasting->getValue();
        }

        $sheetIds = [];
        $sheetTitles = [];
        $sheetFollowers = [];
        $sheetPlans = [];
        $typeTitles = [];
        $spotReferences = [];

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent(
            $query->user,
            $query->event
        );

        foreach ($sheets as $sheet) {
            $sheetIds[] = $sheet->getId();
            $sheetTitles[] = $sheet->getTitle();
            $sheetFollowers[] = $sheet->getFollowerName();

            $mergedOrder = null;
            $plan = null;

            if (null !== $this->merger->getMergedOrders($sheet)) {
                $mergedOrder = $this->merger->getMergedOrders($sheet);
                $plan = $mergedOrder->getPlan();
                if (null !== $plan) {
                    $sheetPlans[] = $plan->getName();
                }
            }

            if (!isset($typeTitles[$sheet->getType()->getId()])) {
                $typeTitles[$sheet->getType()->getId()] = $sheet->getTypeTitle($query->locale);
            }

            if ($sheet->getSpot() instanceof Spot) {
                $spotReferences[] = $sheet->getSpot()->getReference();
            }
        }

        $userSheetView = new UserSheetView(
            $query->user->getId(),
            $query->user->getAccount()->getGender() ?? '',
            $query->user->getAccount()->getFirstName() ?? '',
            $query->user->getAccount()->getLastName() ?? '',
            $query->user->getEmail(),
            $query->user->getMobile() ?? '',
            implode(',', $sheetIds),
            implode(',', $sheetTitles),
            implode(',', $sheetFollowers),
            implode(',', $sheetPlans),
            implode(',', $typeTitles),
            implode(',', $spotReferences),
            $comment,
            $tasting
        );

        return $userSheetView;
    }
}
