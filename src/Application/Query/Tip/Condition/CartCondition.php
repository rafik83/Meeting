<?php

namespace Proximum\Vimeet\Application\Query\Tip\Condition;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class CartCondition implements ConditionInterface
{
    /** @var CartRowRepositoryInterface */
    private $cartRowRepository;

    public function __construct(CartRowRepositoryInterface $cartRowRepository)
    {
        $this->cartRowRepository = $cartRowRepository;
    }

    public function isSatisfiedBy(TipTranslationViewQuery $query, TipTranslationView $tipTranslationView): bool
    {
        if (null === $tipTranslationView->conditionHasCart) {
            return true;
        }

        $hasCart = !empty($this->cartRowRepository->findBySheet($query->sheet));

        if (true === $tipTranslationView->conditionHasCart) {
            return $hasCart;
        }

        return !$hasCart;
    }
}
