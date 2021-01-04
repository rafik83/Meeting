<?php

namespace Proximum\Vimeet\Domain\Order\Numero;

class OrderNumeroView
{
    /**
     * @var int
     */
    public $eventId;

    /**
     * @var int
     */
    public $sheetId;

    /**
     * @var int
     */
    public $orderId;

    /**
     * @param int $eventId
     * @param int $sheetId
     * @param int $orderId
     */
    public function __construct($eventId, $sheetId, $orderId)
    {
        $this->eventId = $eventId;
        $this->sheetId = $sheetId;
        $this->orderId = $orderId;
    }
}
