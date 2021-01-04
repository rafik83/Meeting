<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Model\PromotionCode as ModelPromotionCode;
use Proximum\Vimeet\Domain\Order\Numero\Generator as NumeroGenerator;

/**
 * "Commande"
 */
class Order
{
    /** @var int */
    private $id;

    /** @var Sheet */
    private $sheet;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var float */
    private $vatRate;

    /** @var string */
    private $currency;

    /** @var ArrayCollection Order\Row */
    private $rows = [];

    /** @var ArrayCollection of Order\PromotionCode */
    private $promotionCodes = [];

    /** @var string */
    private $groupsData;

    /** @var bool */
    private $cancelled = false;

    /** @var Invoice|null */
    private $invoice;

    /**
     * @param Sheet              $sheet
     * @param string             $groupsData
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Sheet $sheet,
        $groupsData,
        \DateTimeInterface $createdAt
    ) {
        $this->sheet          = $sheet;
        $this->createdAt      = $createdAt;
        $this->groupsData     = $groupsData;
        $this->currency       = $sheet->getEvent()->getCurrency();
        $this->vatRate        = $sheet->getEvent()->getVat();
        $this->rows           = new ArrayCollection();
        $this->promotionCodes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNumero(): string
    {
        return NumeroGenerator::generate($this);
    }

    /**
     * @return Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * Get Event vatMode
     *
     * @return string
     */
    public function getVatMode(): string
    {
        return $this->getSheet()->getEvent()->getMode();
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Get vatRate
     *
     * @return float
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getGroupsData()
    {
        return $this->groupsData;
    }

    /**
     * @return Row[]
     */
    public function getRows(): array
    {
        return $this->rows->toArray();
    }

    /**
     * @param Row $row
     *
     * @return Order
     */
    public function addRow(Row $row)
    {
        $this->rows->add($row);

        return $this;
    }

    /**
     * @param Order\PromotionCode $promotionCode
     *
     * @return Order
     */
    public function addPromotionCode(Order\PromotionCode $promotionCode): Order
    {
        $this->promotionCodes->add($promotionCode);

        return $this;
    }

    /**
     * @param Row $customRow
     *
     * @return Order
     */
    public function addCustomRow(Row $customRow): Order
    {
        $this->rows->add($customRow);

        return $this;
    }

    /**
     * @param Row $rowToRemove
     *
     * @return self
     */
    public function removeRow(Row $rowToRemove): Order
    {
        foreach ($this->rows as $key => $row) {
            if ($row->getId() === $rowToRemove->getId() && 0 === $row->getQuantity()) {
                $this->rows->remove($key);

                return $this;
            }
        }

        return $this;
    }

    /**
     * @param Row $customRow
     *
     * @return self
     */
    public function removeCustomRow(Row $customRow): Order
    {
        return $this->removeRow($customRow);
    }

    /**
     * @return float
     */
    public function getTotalWithoutVat(): float
    {
        $total = 0;

        /** @var Row $row */
        foreach ($this->getRows() as $row) {
            $total += $row->getQuantity() * $row->getPrice();
        }

        /** @var Order\PromotionCode $promotionCode */
        foreach ($this->promotionCodes->toArray() as $promotionCode) {
            $total += $promotionCode->getPrice();
        }

        return $total;
    }

    public function getGroupLabel(?int $groupId, string $locale, ?string $fallback = null): string
    {
        $data = json_decode($this->groupsData, true);

        if (!isset($data[$groupId]) || !isset($data[$groupId]['translations'])) {
            return '';
        }

        if (isset($data[$groupId]['translations'][$locale])
            && isset($data[$groupId]['translations'][$locale]['label'])
        ) {
            return $data[$groupId]['translations'][$locale]['label'];
        }

        if (null !== $fallback
            && isset($data[$groupId]['translations'][$fallback])
            && isset($data[$groupId]['translations'][$fallback]['label'])
        ) {
            return $data[$groupId]['translations'][$fallback]['label'];
        }

        return '';
    }

    public function getGroups(): array
    {
        return json_decode($this->groupsData, true);
    }

    public function setGroups(array $groups): void
    {
        $this->groupsData = json_encode($groups);
    }

    /**
     * @param int $groupId
     *
     * @return int|null
     */
    public function getGroupRank($groupId)
    {
        $data = json_decode($this->groupsData, true);

        return (isset($data[$groupId]) && isset($data[$groupId]['rank'])) ? $data[$groupId]['rank'] : null;
    }

    /**
     * @return array
     */
    public function getGroupsIds(): array
    {
        return array_keys(json_decode($this->groupsData, true));
    }

    /**
     * @return false|Order\Row[]
     */
    public function getProductRowsForGroupId(string $type, ?int $groupId)
    {
        return array_filter($this->getRows(), function (Order\Row $row) use ($groupId, $type) {
            return $row->isProduct() && $type === $row->getType() && $row->getGroupId() === $groupId;
        });
    }

    /**
     * @param int $groupId
     *
     * @return false|Order\Row[]
     */
    public function getCustomRowsForGroupId($groupId)
    {
        return array_filter($this->getRows(), function (Order\Row $row) use ($groupId) {
            return !$row->isProduct() && $row->getGroupId() === $groupId && !$row->hasParentRow();
        });
    }

    /**
     * @param Row $parentRow
     *
     * @return false|Order\Row[]
     */
    public function getCustomRowsForProduct(Row $parentRow)
    {
        return array_filter($this->getRows(), function (Order\Row $row) use ($parentRow) {
            return !$row->isProduct() && null !== $row->getParentRow() && $parentRow->getId() === $row->getParentRow()->getId();
        });
    }

    /**
     * @return false|Order\Row[]
     */
    public function getRowsWithoutParent()
    {
        return array_filter($this->getRows(), function (Order\Row $row) {
            return !$row->hasParentRow();
        });
    }

    /**
     * @return false|Order\Row[]
     */
    public function getRowsWithParent()
    {
        return array_filter($this->getRows(), function (Order\Row $row) {
            return $row->hasParentRow();
        });
    }

    /**
     * @param Product|null $product
     *
     * @return null|Order\Row
     */
    public function getRowForProduct(Product $product = null)
    {
        if (null === $product) {
            return null;
        }

        foreach ($this->rows as $row) {
            if (null !== $row->getProduct() && $row->getProduct() === $product) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param null|int $id
     *
     * @return null|Order\Row
     */
    public function getRowByProductId($id)
    {
        foreach ($this->rows as $row) {
            if (null !== $row->getProduct()
                && $row->getProduct()->getId() === $id
            ) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasType($type)
    {
        return \count($this->getRowsProductOfType($type));
    }

    /**
     * @param string $type
     *
     * @return Order\Row[]
     */
    public function getRowsProductOfType(string $type): array
    {
        return array_filter($this->getRows(), function (Order\Row $row) use ($type) {
            return $type === $row->getType();
        });
    }

    /**
     * @return Order\Row[]
     */
    public function getRowsProductOfParticipantType(): array
    {
        return $this->getRowsProductOfType(Product::TYPE_PARTICIPANT);
    }

    /**
     * @return Order\PromotionCode[]
     */
    public function getPromotionCodes()
    {
        return $this->promotionCodes->toArray();
    }

    public function removePromotionCode(Order\PromotionCode $promotionCode)
    {
        return $this->promotionCodes->removeElement($promotionCode);
    }

    /**
     * @param Order\PromotionCode|ModelPromotionCode $promotionCode
     *
     * @return bool
     */
    public function hasPromotionCode($promotionCode)
    {
        foreach ($this->promotionCodes as $promoCode) {
            if ($promotionCode instanceof ModelPromotionCode
                && $promoCode->getPromotionCode() === $promotionCode
            ) {
                return true;
            } elseif ($promotionCode instanceof Order\PromotionCode
                && $promoCode === $promotionCode
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function countParticipant()
    {
        $participant = 0;
        foreach ($this->rows as $orderRow) {
            if (null !== $orderRow->getProduct()
                && Product::TYPE_PARTICIPANT === $orderRow->getProduct()->getType()
            ) {
                $participant += $orderRow->getQuantity();
            }
        }

        return $participant;
    }

    /**
     * @return int
     */
    public function countPlanning(): int
    {
        $planning = 0;

        /** @var Row $orderRow */
        foreach ($this->rows as $orderRow) {
            if (null !== $orderRow->getProduct()
                && Product::TYPE_PLANNING === $orderRow->getProduct()->getType()
            ) {
                $planning += $orderRow->getQuantity();
            }
        }

        $plan = $this->getPlan();

        if ($plan instanceof Product && Product::TYPE_PLAN === $plan->getType()) {
            $planning += $plan->getIncludedPlanningQuantity();
        }

        return $planning;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasPromotionCodeForProduct(Product $product)
    {
        /** @var Order\PromotionCode $promotionCode */
        foreach ($this->promotionCodes->toArray() as $promotionCode) {
            if ($promotionCode->getPromotionCode()->hasPromotion($product)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get product plan in order
     *
     * @return null|Product
     */
    public function getPlan()
    {
        foreach ($this->rows as $row) {
            if (Product::TYPE_PLAN === $row->getType()) {
                return $row->getProduct();
            }
        }

        return null;
    }

    /**
     * Get product option in order
     *
     * @return Product[]
     */
    public function getOptions(): array
    {
        $options = [];
        foreach ($this->rows as $row) {
            if (Product::TYPE_OPTION === $row->getType()) {
                $options[] = $row->getProduct();
            }
        }

        return $options;
    }

    /**
     * @return Product\ProductIncluded[]
     */
    public function getIncludedParticipantProducts(): array
    {
        if (null === $this->getPlan()) {
            return [];
        }

        return $this->getPlan()->getIncludedParticipantProducts();
    }

    /**
     * @return Product\ProductIncluded[]
     */
    public function getIncludedAttributableOptionProducts(): array
    {
        if (null === $this->getPlan()) {
            return [];
        }

        return $this->getPlan()->getIncludedAttributableOptionProducts();
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    /**
     * Set cancelled
     */
    public function cancel(): void
    {
        $this->cancelled = true;
    }

    /**
     * @return Invoice|null
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param Invoice $invoice
     */
    public function setInvoice(Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    /**
     * @return bool
     */
    public function hasInvoice(): bool
    {
        return null !== $this->getInvoice();
    }

    /**
     * @param Sheet              $sheet
     * @param \DateTimeInterface $dateTime
     *
     * @return Order
     */
    public static function createFromSheet(Sheet $sheet, \DateTimeInterface $dateTime): Order
    {
        return new self(
            $sheet,
            '[]',
            $dateTime
        );
    }
}
