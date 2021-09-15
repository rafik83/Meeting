<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\ParticipantAndPlanningGroupView;

class ParticipantAndPlanningGroupViewQueryHandler
{
    /**
     * @var ProductViewQueryHandler
     */
    private $productViewQueryHandler;

    /**
     * @param ProductViewQueryHandler $productViewQueryHandler
     */
    public function __construct(ProductViewQueryHandler $productViewQueryHandler)
    {
        $this->productViewQueryHandler = $productViewQueryHandler;
    }

    /**
     * @param ParticipantAndPlanningGroupViewQuery $participantAndPlanningGroupViewQuery
     *
     * @throws \Exception
     *
     * @return ParticipantAndPlanningGroupView
     */
    public function handle(ParticipantAndPlanningGroupViewQuery $participantAndPlanningGroupViewQuery)
    {
        $cart    = $participantAndPlanningGroupViewQuery->cart;
        $package = $participantAndPlanningGroupViewQuery->sheet->getPackage();

        if (!$package->isParticipantAndPlanningEnabled()) {
            throw new \Exception('Participant is not enabled');
        }

        $productViews = [];

        foreach ($cart->getParticipantRows() as $participantRow) {
            $productViews[] = $this->productViewQueryHandler->handle(
                new ProductViewQuery(
                    $participantAndPlanningGroupViewQuery->sheet,
                    $participantRow->getProduct(),
                    $cart,
                    $participantAndPlanningGroupViewQuery->locale,
                    $participantAndPlanningGroupViewQuery->planGroupView
                )
            );
        }

        $planningRow = $cart->getPlanningRow();

        if (null !== $planningRow) {
            $productViews[] = $this->productViewQueryHandler->handle(
                new ProductViewQuery(
                    $participantAndPlanningGroupViewQuery->sheet,
                    $planningRow->getProduct(),
                    $cart,
                    $participantAndPlanningGroupViewQuery->locale,
                    $participantAndPlanningGroupViewQuery->planGroupView
                )
            );
        }

        $total = 0;

        foreach ($productViews as $participantView) {
            $total += $participantView->total;
        }

        return new ParticipantAndPlanningGroupView(
            $package->getParticipantAndPlanningLabel($participantAndPlanningGroupViewQuery->locale),
            $productViews,
            $total
        );
    }
}
