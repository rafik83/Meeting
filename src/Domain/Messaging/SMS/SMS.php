<?php

namespace Proximum\Vimeet\Domain\Messaging\SMS;

class SMS
{
    /**
     * Receiver phone number
     *
     * @var string
     */
    private $receiver;

    /** @var string body */
    private $message;

    /** @var bool */
    private $stopClause;

    /**
     * @param string $receiver
     * @param string $message
     * @param bool   $stopClause
     */
    public function __construct($receiver, $message, bool $stopClause = false)
    {
        $this->receiver = $receiver;
        $this->message = $message;
        $this->stopClause = $stopClause;
    }

    /**
     * @return string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function hasStopClause(): bool
    {
        return $this->stopClause;
    }
}
