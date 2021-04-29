<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class OrderManager
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    private ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository;
    private ParticipantRepositoryInterface $participantRepository;
    /** @var SheetManager */
    private $sheetManager;

    /** @var BillingInfoManager */
    private $billingInfoManager;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        ParticipantRepositoryInterface $participantRepository,
        SheetManager $sheetManager,
        BillingInfoManager $billingInfoManager,
        \DateTimeInterface $dateTime
    ) {
        $this->orderRepository = $orderRepository;
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
        $this->participantRepository = $participantRepository;
        $this->sheetManager = $sheetManager;
        $this->billingInfoManager = $billingInfoManager;
        $this->dateTime = $dateTime;
    }

    /**
     * @throws \Exception
     */
    public function createOrderOfGivenTotal(
        Event $event,
        $total,
        bool $isVatApplicable,
        ?Sheet $sheet = null
    ): Order {
        if (null === $sheet) {
            $sheet = $this->sheetManager->create($event);

            if ('FR' !== $event->getCountry()) {
                throw new \Exception('Event must have country=FR in order to manage is VAT applicable or not.');
            }

            if (true === $isVatApplicable) {
                $this->billingInfoManager->create($sheet, 'FR');
            } else {
                $this->billingInfoManager->create($sheet, 'US');
            }
        }

        $groupsData = json_encode(['1' => ['translations' => ['fr' => ['label' => 'Options de communication']], 'rank' => 0]]);
        $order = new Order($sheet, $groupsData, $this->dateTime);
        $this->createOrderRow($order, 'My product label', $total, 1, $sheet->getEvent()->getVat());
        $this->orderRepository->add($order);

        return $order;
    }

    public function createOrderRow(Order $order, string $label, float $price, int $quantity, float $vatRate): Order\Row
    {
        $orderRow = new Order\Row($order, $quantity, $vatRate, null, null, $label, $price);
        $order->addRow($orderRow);

        return $orderRow;
    }

    public function createOrderProductRow(Order $order, Product $product, int $quantity = 1): Order\Row
    {
        $orderRow = new Order\Row($order, $quantity, $product->getVat(), $product, null, $product->getTitle('fr'), $product->getUnitPrice());
        $order->addRow($orderRow);
        $this->orderRepository->set($order);

        return $orderRow;
    }

    public function attributeProductToParticipant(Order\Row $orderRow, Product $product, Sheet $sheet): void
    {
        $participantsWithoutProduct = array_filter($sheet->getParticipantsArray(), fn (Participant $p) => $p->hasParticipantProduct());

        if (empty($participantsWithoutProduct)) {
            return;
        }

        $participant = $participantsWithoutProduct[0];

        $productAttributedToParticipant = new ProductAttributedToParticipant(
            $product,
            $participant,
            new \DateTime(),
            $orderRow
        );
        $this->productAttributedToParticipantRepository->add($productAttributedToParticipant);

        $participant->setParticipantProduct($product);
        $this->participantRepository->set($participant);
    }
}
