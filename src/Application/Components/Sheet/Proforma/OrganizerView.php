<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Proforma;

use Proximum\Vimeet\Domain\Model\Address;

class OrganizerView
{
    /**
     * @var string
     */
    public $organiserName;

    /**
     * @var Address
     */
    public $paymentAddress;

    /**
     * @var string
     */
    public $organiserEmail;

    /**
     * @var string
     */
    public $bankInfo;

    /**
     * @var string
     */
    public $legalInformation;

    /**
     * @var string
     */
    public $elementToJoinWithInvoice;

    /**
     * OrganizerView constructor.
     *
     * @param string  $organiserName
     * @param Address $paymentAddress
     * @param string  $organiserEmail
     * @param string  $bankInfo
     * @param string  $legalInformation
     * @param string  $elementToJoinWithInvoice
     */
    public function __construct($organiserName, Address $paymentAddress, $organiserEmail, $bankInfo, $legalInformation, $elementToJoinWithInvoice)
    {
        $this->organiserName            = $organiserName;
        $this->paymentAddress           = $paymentAddress;
        $this->organiserEmail           = $organiserEmail;
        $this->bankInfo                 = $bankInfo;
        $this->legalInformation         = $legalInformation;
        $this->elementToJoinWithInvoice = $elementToJoinWithInvoice;
    }
}
