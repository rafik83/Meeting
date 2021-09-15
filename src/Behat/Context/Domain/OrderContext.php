<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use InvalidArgumentException;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\OrderContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;

class OrderContext implements Context
{
    /** @var OrderContextProxyInterface */
    private $orderContextProxy;

    /**
     * @param OrderContextProxyInterface $orderContextProxy
     */
    public function __construct(OrderContextProxyInterface $orderContextProxy)
    {
        $this->orderContextProxy = $orderContextProxy;
    }

    /**
     * @Given /^there is an order with the amount of (?P<total>\d+) and VAT is applicable$/
     *
     * @param float $total
     */
    public function thereIsAnOrderAndVatIsApplicable($total)
    {
        $this->createOrder($total, true);
    }

    /**
     * @Given /^there is an order with the amount of (?P<total>\d+) for this sheet$/
     *
     * @param float $total
     */
    public function thereIsAnOrderForThisSheet($total)
    {
        $this->createOrderForSheet($total);
    }

    /**
     * @Given /^there is an order with the amount of (?P<total>\d+) and VAT is not applicable$/
     *
     * @param float $total
     */
    public function thereIsAnOrderAndVatIsNotApplicable($total)
    {
        $this->createOrder($total, false);
    }

    /**
     * @Given there is this product Planning for this order
     */
    public function thereIsThisProductPlanningForThisOrder()
    {
        $this->createProductOrder('productPlanning');
    }

    /**
     * @Given there is this product Participant for this order
     */
    public function thereIsThisProductParticipantForThisOrder()
    {
        $this->createProductOrder('productParticipant');
    }

    /**
     * @Given there is this plan for this order
     */
    public function thereIsThisPlanForThisOrder()
    {
        $this->createProductOrder('plan');
    }

    private function createProductOrder(string $productType): Order
    {
        $order = $this->getOrder();

        /** @var Product */
        $product = $this->orderContextProxy->getStorage()->get($productType);
        if (null === $product) {
            throw new \InvalidArgumentException('Missing '.$productType);
        }

        $orderRow = $this->orderContextProxy->getOrderManager()->createOrderProductRow($order, $product);

        if ($product->getType() === Product::TYPE_PARTICIPANT) {
            /** @var Sheet */
            $sheet = $this->orderContextProxy->getStorage()->get('sheet');
            if (null === $sheet) {
                throw new \InvalidArgumentException('Missing Sheet');
            }
            $this->orderContextProxy->getOrderManager()->attributeProductToParticipant($orderRow, $product, $sheet);
        }

        return $order;
    }

    /**
     * @Given there is the option :name :quantity times for this order
     */
    public function thereIsTheOptionTimesForThisOrder(string $name, int $quantity)
    {
        $options = $this->orderContextProxy->getStorage()->get('options');
        $optionsFiltered = array_filter($options, fn (Product $option) => $option->getTitle('fr') === $name);
        if (empty($optionsFiltered)) {
            throw new InvalidArgumentException($name.': Option not found');
        }
        $option = reset($optionsFiltered);

        $this->orderContextProxy->getOrderManager()->createOrderProductRow($this->getOrder(), $option, $quantity);
    }

    private function getOrder(): Order
    {
        $order = $this->orderContextProxy->getStorage()->get('order');
        if (null === $order) {
            throw new \InvalidArgumentException('Missing Order');
        }

        return $order;
    }

    /**
     * @param float $total
     * @param bool  $isVatApplicable
     *
     * @throws \Exception
     */
    private function createOrder(float $total, bool $isVatApplicable = true): void
    {
        $event = $this->orderContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $orderManager = $this->orderContextProxy->getOrderManager();

        $order = $orderManager->createOrderOfGivenTotal($event, $total, $isVatApplicable);
        $this->orderContextProxy->getStorage()->set('order', $order);
    }

    /**
     * @param float $total
     *
     * @throws \Exception
     */
    private function createOrderForSheet(float $total): void
    {
        $event = $this->orderContextProxy->getStorage()->get('event');
        $sheet = $this->orderContextProxy->getStorage()->get('sheet');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $orderManager = $this->orderContextProxy->getOrderManager();

        $order = $orderManager->createOrderOfGivenTotal($event, $total, true, $sheet);
        $this->orderContextProxy->getStorage()->set('order', $order);
    }
}
