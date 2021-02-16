<?php

namespace Proximum\Vimeet\Domain\Badge;

use Proximum\Vimeet\Domain\KeyDates\Checker\EnableBadgeForParticipantDateAccessChecker;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\HasRemainingToPay;

/**
 * Specification to determine if Badge is available for Sheet
 */
class AvailableChecker
{
    /** @var EnableBadgeForParticipantDateAccessChecker */
    private $enableBadgeForParticipantDateAccessChecker;

    /** @var BadgeRepositoryInterface */
    private $badgeRepository;

    /** @var HasRemainingToPay */
    private $hasRemainingToPay;

    public function __construct(
        EnableBadgeForParticipantDateAccessChecker $enableBadgeForParticipantDateAccessChecker,
        BadgeRepositoryInterface $badgeRepository,
        HasRemainingToPay $hasRemainingToPay
    ) {
        $this->enableBadgeForParticipantDateAccessChecker = $enableBadgeForParticipantDateAccessChecker;
        $this->badgeRepository = $badgeRepository;
        $this->hasRemainingToPay = $hasRemainingToPay;
    }

    public function isSatisfiedBy(Sheet $sheet): bool
    {
        if (!$this->enableBadgeForParticipantDateAccessChecker->allowedToAccess($sheet->getEvent())) {
            return false;
        }

        $badge = $this->badgeRepository->findByType($sheet->getType());

        if ($badge instanceof Badge) {
            if (!$badge->isActivated()) {
                return false;
            }

            if ($badge->isConditioned()) {
                if ($badge->isConditionedByPackage()
                    && $this->hasRemainingToPay->isSatisfiedBy($sheet)
                ) {
                    return false;
                }

                if (!empty($badge->getConditionedByStates())
                    && !\in_array($sheet->getState(), $badge->getConditionedByStates(), true)
                ) {
                    return false;
                }
            }
        }

        return true;
    }
}
