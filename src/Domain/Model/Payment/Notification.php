<?php

namespace Proximum\Vimeet\Domain\Model\Payment;

class Notification
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    private $gatewayName;

    /**
     * @var array
     */
    private $details;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * Notification constructor.
     *
     * @param string             $gatewayName
     * @param array              $details
     * @param \DateTimeInterface $createdAt
     */
    public function __construct($gatewayName, array $details, \DateTimeInterface $createdAt)
    {
        $this->details     = $details;
        $this->gatewayName = $gatewayName;
        $this->createdAt   = $createdAt;
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
    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
