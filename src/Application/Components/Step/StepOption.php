<?php

namespace Proximum\Vimeet\Application\Components\Step;

use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Command\Package\Step\SelectOptions;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class StepOption
{
    /** @var Merger */
    private $orderMerger;

    /** @var CartManager */
    private $cartManager;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    public function __construct(
        Merger $orderMerger,
        CartManager $cartManager,
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->orderMerger = $orderMerger;
        $this->cartManager = $cartManager;
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
        $this->dateTime = $dateTime;
    }

    public function build(Sheet $sheet, int $stepIndex): SelectOptions
    {
        $command = new SelectOptions($sheet, $stepIndex);
        $command->options = $this->buildOptions($sheet, $stepIndex);

        return $command;
    }

    /**
     * @return OptionRow[] indexed by Product id
     */
    private function buildOptions(Sheet $sheet, int $stepIndex): array
    {
        $cart = $this->cartManager->getCart($sheet, $stepIndex);
        $orderMerged = null;

        if ($sheet->hasNotCancelledOrders()) {
            $orderMerged = $this->orderMerger->merge($sheet->getNotCancelledOrders());
        }

        /** @var CartRow[] $optionRows */
        $cartRows = [];

        foreach ($cart->getOptionsRowArray() as $optionRow) {
            $cartRows[$optionRow->getProduct()->getId()] = $optionRow;
        }

        $options = [];
        $availableOptions = $sheet->getPackage()->getAvailablesOptions($this->dateTime);

        foreach ($availableOptions as $option) {
            $options[$option->getId()] = new OptionRow(
                $this->getOptionQuantity($option, $cartRows, $orderMerged),
                $this->getOptionParticipant($sheet, $option, $cartRows),
                $option->isAttributable()
            );
        }

        return $options;
    }

    /**
     * @param Product    $option
     * @param CartRow[]  $cartRows
     * @param null|Order $order
     *
     * @return int
     */
    private function getOptionQuantity(Product $option, array $cartRows = [], Order $order = null): int
    {
        $optionQuantity = 0;
        $cartRow = $this->getCartRowFromOption($option, $cartRows);

        if (null !== $cartRow) {
            $optionQuantity = $cartRow->getQuantity();
        }

        return $this->getQuantityFromOrder($order, $option) + $optionQuantity;
    }

    /**
     * @param Sheet     $sheet
     * @param Product   $option
     * @param CartRow[] $cartRows
     *
     * @return Participant[]
     */
    private function getOptionParticipant(Sheet $sheet, Product $option, array $cartRows = []): array
    {
        if (!$option->isAttributable()) {
            return [];
        }

        $cartRow = $this->getCartRowFromOption($option, $cartRows);

        if (null !== $cartRow) {
            return $cartRow->getParticipants();
        }

        $productAttributedToParticipants = $this->productAttributedToParticipantRepository->findByProductAndParticipants(
            $option,
            $sheet->getParticipantsArray()
        );

        return array_map(static function (ProductAttributedToParticipant $productAttributedToParticipant) {
            return $productAttributedToParticipant->getParticipant();
        }, $productAttributedToParticipants);
    }

    /**
     * @param Order|null $order
     * @param Product    $option
     *
     * @return int
     */
    private function getQuantityFromOrder(Order $order = null, Product $option): int
    {
        if (!$order instanceof Order || !$orderRow = $order->getRowForProduct($option)) {
            return 0;
        }

        return $orderRow->getQuantity();
    }

    /**
     * @param Product   $option
     * @param CartRow[] $cartRows
     *
     * @return CartRow|null
     */
    private function getCartRowFromOption(Product $option, array $cartRows): ?CartRow
    {
        if (isset($cartRows[$option->getId()])) {
            return $cartRows[$option->getId()];
        }

        return null;
    }
}
