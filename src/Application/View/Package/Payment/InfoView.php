<?php

namespace Proximum\Vimeet\Application\View\Package\Payment;

class InfoView
{
    /** @var string|null */
    public $organiserName;

    /** @var string|null */
    public $billingAddress;

    /** @var string|null */
    public $organiserEmail;

    /** @var string|null */
    public $bankInfo;

    /** @var string|null */
    public $paymentCondition;

    public function __construct(
        ?string $organiserName,
        ?string $billingAddress,
        ?string $organiserEmail,
        ?string $bankInfo,
        ?string $paymentCondition
    ) {
        $this->organiserName = $organiserName;
        $this->billingAddress = $billingAddress;
        $this->organiserEmail = $organiserEmail;
        $this->bankInfo = $bankInfo;
        $this->paymentCondition = $paymentCondition;
    }
}
