<?php

namespace Proximum\Vimeet\Domain\Model\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Type;

class PaymentConditions
{
    /** @var int */
    private $id;

    /** @var Type */
    private $type;

    /** @var bool */
    private $allowDeposit = false;

    /** @var \DateTimeInterface|null */
    private $depositUntil;

    /** @var float|null */
    private $minimumForDeposit;

    /** @var int|null */
    private $deposit;

    /** @var array */
    private $paymentModes;

    /** @var ArrayCollection of PaymentConditionsTranslation */
    private $translations;

    public function __construct(
        Type $type,
        array $paymentModes = [],
        bool $allowDeposit,
        \DateTimeInterface $depositUntil = null,
        float $minimumForDeposit = null,
        int $deposit = null
    ) {
        $this->type              = $type;
        $this->paymentModes      = $paymentModes;
        $this->allowDeposit      = $allowDeposit;
        $this->depositUntil      = $depositUntil;
        $this->minimumForDeposit = $minimumForDeposit;
        $this->deposit           = $deposit;
        $this->translations      = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isAllowDeposit(): bool
    {
        return $this->allowDeposit;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDepositUntil()
    {
        return $this->depositUntil;
    }

    /**
     * @return float|null
     */
    public function getMinimumForDeposit()
    {
        return $this->minimumForDeposit;
    }

    /**
     * @return int|null
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * @return array
     */
    public function getPaymentModes(): array
    {
        return $this->paymentModes;
    }

    public function update(
        array $paymentModes = [],
        bool $allowDeposit,
        \DateTimeInterface $depositUntil = null,
        float $minimumForDeposit = null,
        int $deposit = null
    ) {
        $this->paymentModes = array_values($paymentModes);
        $this->allowDeposit = $allowDeposit;
        $this->depositUntil = $depositUntil;
        $this->minimumForDeposit = $minimumForDeposit;
        $this->deposit = $deposit;
    }

    public function updateTranslations(array $translations): void
    {
        foreach ($translations as $locale => $translation) {
            $this->translate(
                $locale,
                $translation['bankInfo'],
                $translation['billingAddress'],
                $translation['paymentCondition'],
                $translation['paymentFooter']
            );
        }
    }

    public function hasTranslation(string $locale): bool
    {
        return $this->translations->containsKey($locale);
    }

    public function getTranslation(string $locale): PaymentConditionsTranslation
    {
        return $this->translations->get($locale);
    }

    public function translate(
        string $locale,
        string $bankInfo,
        string $billingAddress,
        string $paymentCondition,
        string $paymentFooter
    ): void {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->set($bankInfo, $billingAddress, $paymentCondition, $paymentFooter);
        } else {
            $this->setTranslation($locale, $bankInfo, $billingAddress, $paymentCondition, $paymentFooter);
        }
    }

    public function setTranslation(
        string $locale,
        string $bankInfo,
        string $billingAddress,
        string $paymentCondition,
        string $paymentFooter
    ): void {
        $this->translations->set(
            $locale,
            new PaymentConditionsTranslation(
                $this,
                $locale,
                $bankInfo,
                $billingAddress,
                $paymentCondition,
                $paymentFooter
            )
        );
    }

    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function getBankInfo(string $locale): string
    {
        if (!$this->translations->containsKey($locale)) {
            return '';
        }

        return $this->translations->get($locale)->getBankInfo();
    }

    public function getBillingAddress(string $locale): string
    {
        if (!$this->translations->containsKey($locale)) {
            return '';
        }

        return $this->translations->get($locale)->getBillingAddress();
    }

    public function getPaymentCondition(string $locale): string
    {
        if (!$this->translations->containsKey($locale)) {
            return '';
        }

        return $this->translations->get($locale)->getPaymentCondition();
    }

    public function getPaymentFooter(string $locale): string
    {
        if (!$this->translations->containsKey($locale)) {
            return '';
        }

        return $this->translations->get($locale)->getPaymentFooter();
    }
}
