<?php

namespace Proximum\Vimeet\Domain\Package\Funnel;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;

class FunnelFactory
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var CartStepRepositoryInterface
     */
    private $cartStepRepository;

    /**
     * @param CartManager                 $cartManager
     * @param CartStepRepositoryInterface $cartStepRepository
     */
    public function __construct(CartManager $cartManager, CartStepRepositoryInterface $cartStepRepository)
    {
        $this->cartManager        = $cartManager;
        $this->cartStepRepository = $cartStepRepository;
    }

    /**
     * Create a funnel with its steps
     *
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return Funnel
     */
    public function create(Sheet $sheet, $locale)
    {
        $package = $sheet->getPackage();
        $funnel  = new Funnel($sheet);

        $cartStep = $this->cartStepRepository->findBySheet($sheet);
        $cart     = $this->cartManager->getCart($sheet);

        if (null !== $cartStep) {
            $cart->setCurrentStep($cartStep->getCurrentStep());
        }

        $funnel->setCart($cart);
        $funnel->setCartStep($cartStep);

        if ($package->isPlansEnabled() && !$sheet->hasNotCancelledOrders()) {
            $step = new Step($this->getNextIndex($funnel), $package->getPlansLabel($locale), Step::TYPE_PLAN);

            if (null !== $cart->getCurrentStep() && $cart->getCurrentStep() > $this->getNextIndex($funnel)) {
                $step->completed = true;
            }

            $funnel->addStep($step);
        }

        if ($package->isParticipantAndPlanningEnabled()) {
            $step = new Step($this->getNextIndex($funnel), $package->getParticipantAndPlanningLabel($locale), Step::TYPE_PARTICIPANT_PLANNING);

            if (null !== $cart->getCurrentStep() && $cart->getCurrentStep() > $this->getNextIndex($funnel)) {
                $step->completed = true;
            }

            $funnel->addStep($step);
        }

        if ($package->isOptionsEnabled()) {
            $step = new Step($this->getNextIndex($funnel), $package->getOptionsLabel($locale), Step::TYPE_OPTIONS);

            if (null !== $cart->getCurrentStep() && $cart->getCurrentStep() > $this->getNextIndex($funnel)) {
                $step->completed = true;
            }

            $funnel->addStep($step);
        }

        return $funnel;
    }

    /**
     * Get the next available index
     *
     * @param Funnel $funnel
     *
     * @return int
     */
    private function getNextIndex(Funnel $funnel)
    {
        return count($funnel->getSteps()) + 1;
    }
}
