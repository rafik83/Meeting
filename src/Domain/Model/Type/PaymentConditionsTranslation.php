<?php

namespace Proximum\Vimeet\Domain\Model\Type;

class PaymentConditionsTranslation
{
    /** @var int */
    private $id;

    /** @var PaymentConditions */
    private $paymentConditions;

    /** @var string */
    private $locale;

    /** @var string */
    private $bankInfo;

    /** @var string */
    private $billingAddress;

    /** @var string */
    private $paymentCondition;

    /** @var string */
    private $paymentFooter;

    public function __construct(
        PaymentConditions $paymentConditions,
        string $locale,
        string $bankInfo,
        string $billingAddress,
        string $paymentCondition,
        string $paymentFooter
    ) {
        $this->paymentConditions = $paymentConditions;
        $this->locale = $locale;
        $this->bankInfo = $bankInfo;
        $this->billingAddress = $billingAddress;
        $this->paymentCondition = $paymentCondition;
        $this->paymentFooter = $paymentFooter;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaymentConditions(): PaymentConditions
    {
        return $this->paymentConditions;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getBankInfo(): string
    {
        return $this->bankInfo;
    }

    public function getBillingAddress(): string
    {
        return $this->billingAddress;
    }

    public function getPaymentCondition(): string
    {
        return $this->paymentCondition;
    }

    public function getPaymentFooter(): string
    {
        return $this->paymentFooter;
    }

    public function set(
        string $bankInfo,
        string $billingAddress,
        string $paymentCondition,
        string $paymentFooter
    ): void {
        $this->bankInfo = $bankInfo;
        $this->billingAddress = $billingAddress;
        $this->paymentCondition = $paymentCondition;
        $this->paymentFooter = $paymentFooter;
    }
}
