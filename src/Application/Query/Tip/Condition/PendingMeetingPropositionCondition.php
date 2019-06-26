<?php

namespace Proximum\Vimeet\Application\Query\Tip\Condition;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class PendingMeetingPropositionCondition implements ConditionInterface
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    public function isSatisfiedBy(TipTranslationViewQuery $query, TipTranslationView $tipTranslationView): bool
    {
        if (null === $tipTranslationView->conditionHasPendingMeetingProposition) {
            return true;
        }

        $hasPendingPropositionReceivedBySheet = $this->requestRepository->hasPendingPropositionReceivedBySheet(
            $query->sheet
        );

        return $tipTranslationView->conditionHasPendingMeetingProposition === $hasPendingPropositionReceivedBySheet;
    }
}
