<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
     * @var \DateTime
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
}
