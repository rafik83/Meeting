<?php

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Payment\Mode;

/**
 * "Transaction".
 */
class Transaction
{
    const STATE_PENDING   = 'pending';
    const STATE_PAID      = 'paid';
    const STATE_CANCELLED = 'cancelled';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * See Mode constants
     *
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $reference;

    // For some payment gateway, we need to store additionnal info (like order ids for CCIP)
    private string $internalReference = '';

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var Payment|null
     */
    private $payment;

    /**
     * @var bool
     */
    private $hidden = false;

    /**
     * @param Sheet              $sheet
     * @param float              $amount
     * @param \DateTimeInterface $date
     * @param string             $mode      one of Mode constants
     * @param null|string        $reference
     * @param string             $state
     * @param string             $currency
     * @param User|null          $user
     * @param bool               $hidden
     */
    public function __construct(
        Sheet $sheet,
        $amount,
        \DateTimeInterface $date,
        $mode,
        $reference,
        $state,
        $currency,
        User $user = null,
        $hidden = false
    ) {
        $this->sheet     = $sheet;
        $this->amount    = $amount;
        $this->date      = $date;
        $this->mode      = $mode;
        $this->reference = $reference;
        $this->state     = $state;
        $this->currency  = $currency;
        $this->user      = $user;
        $this->hidden    = $hidden;
    }

    /**
     * Update transaction
     *
     * @param float              $amount
     * @param \DateTimeInterface $date
     * @param string             $reference
     * @param string             $state
     *
     * @return Transaction
     */
    public function update($amount, \DateTimeInterface $date, $reference, $state)
    {
        $this->amount    = $amount;
        $this->date      = $date;
        $this->reference = $reference;
        $this->state     = $state;

        return $this;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get sheet
     *
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function getAmountInCents()
    {
        return (int) round(100 * $this->amount);
    }

    /**
     * Get date
     *
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return !$this->user ? $this->sheet->getOwner() : $this->user;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return null|Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return self::STATE_PENDING === $this->state;
    }

    /**
     * @return bool
     */
    public function isPaid()
    {
        return self::STATE_PAID === $this->state;
    }

    /**
     * @return bool
     */
    public function isPaypal()
    {
        return Mode::PAYMENT_PAYPAL === $this->getMode();
    }

    /**
     * @return bool
     */
    public function isCCIP()
    {
        return Mode::PAYMENT_CCIP === $this->getMode();
    }

    /**
     * Set state to Paid
     */
    public function setPaid()
    {
        $this->state = self::STATE_PAID;
    }

    /**
     * Set state to cancelled
     */
    public function setCancelled()
    {
        $this->state = self::STATE_CANCELLED;
    }

    /**
     * @return bool
     */
    public function isRequiredPaymentInfo()
    {
        return in_array($this->mode, Mode::getModeThatRequiredPaymentInfo());
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * Change the option hidden to false
     */
    public function unHide()
    {
        $this->hidden = false;
    }

    public function getInternalReference(): string
    {
        return $this->internalReference;
    }

    public function setInternalReference(string $internalReference)
    {
        $this->internalReference = $internalReference;
    }

    /**
     * @param Sheet              $sheet
     * @param User               $user
     * @param float              $amount
     * @param \DateTimeInterface $date
     *
     * @return Transaction
     */
    public static function createForPaypal(Sheet $sheet, User $user, $amount, \DateTimeInterface $date): Transaction
    {
        return new self(
            $sheet,
            $amount,
            $date,
            Mode::PAYMENT_PAYPAL,
            null,
            self::STATE_PENDING,
            $sheet->getEvent()->getCurrency(),
            $user,
            true
        );
    }

    /**
     * @param \DateTimeInterface $date
     * @param int[] $orderIds we need to store this information for CCIP payments,
     *                        in order to add order info in payment process
     */
    public static function createForCcip(Sheet $sheet, User $user, float $amount, \DateTimeInterface $date, array $orderIds): Transaction
    {
        $transaction = new self(
            $sheet,
            $amount,
            $date,
            Mode::PAYMENT_CCIP,
            null,
            self::STATE_PENDING,
            $sheet->getEvent()->getCurrency(),
            $user,
            true
        );

        $transaction->setInternalReference(implode(',', $orderIds));

        return $transaction;
    }
}
