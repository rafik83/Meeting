<?php

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipOpened;
use Proximum\Vimeet\Domain\Repository\Tip\TipOpenedRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class IsTipOpened
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var TipOpenedRepositoryInterface */
    private $tipOpenedRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        TipRepositoryInterface $tipRepository,
        TipOpenedRepositoryInterface $tipOpenedRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->tipRepository = $tipRepository;
        $this->tipOpenedRepository = $tipOpenedRepository;
        $this->dateTime = $dateTime;
    }

    public function isSatisfiedBy(TipTranslationViewQuery $query, TipTranslationView $tipTranslationView): bool
    {
        if (Tip::DISPLAY_DEFAULT === $tipTranslationView->display) {
            return false;
        }

        $tip = $this->tipRepository->getById($tipTranslationView->id);

        if (null === $tip) {
            return false;
        }

        if (Tip::DISPLAY_ALWAYS_OPENED === $tipTranslationView->display) {
            return $this->handleAlwaysOpenedTip($tip, $query);
        }

        if (Tip::DISPLAY_FIRST_TIME_OPENED === $tipTranslationView->display) {
            return $this->handleFirstTimeOpenedTip($tip, $query);
        }

        return false;
    }

    private function handleAlwaysOpenedTip(Tip $tip, TipTranslationViewQuery $query): bool
    {
        $tipOpened = $this->tipOpenedRepository->getByTipAndUser($tip, $query->user);

        if (null === $tipOpened) {
            $this->tipOpenedRepository->add(new TipOpened($query->user, $tip, $this->dateTime));

            return true;
        }

        if ($tipOpened->isOpenedForMoreThanTwoHours($this->dateTime)) {
            $tipOpened->updateOpenedAt($this->dateTime);
            $this->tipOpenedRepository->set($tipOpened);

            return true;
        }

        return false;
    }

    private function handleFirstTimeOpenedTip(Tip $tip, TipTranslationViewQuery $query): bool
    {
        if ($this->tipOpenedRepository->isOpened($tip, $query->user)) {
            return false;
        }

        $this->tipOpenedRepository->add(new TipOpened($query->user, $tip, $this->dateTime));

        return true;
    }
}
