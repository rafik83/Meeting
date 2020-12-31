<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service;

use Proximum\Vimeet\Domain\Model\Event;

class EventSender
{
    const MAIL = 'no-reply';

    /**
     * @var string
     */
    private $applicationDomain;

    /**
     * @var string
     */
    private $defaultSender;

    /**
     * EventSender constructor.
     *
     * @param string $applicationDomain
     * @param string $defaultSender
     */
    public function __construct($applicationDomain, $defaultSender)
    {
        $this->applicationDomain = $applicationDomain;
        $this->defaultSender     = $defaultSender;
    }

    /**
     * @param Event|null $event
     *
     * @return string
     */
    public function generate(Event $event = null)
    {
        if (null === $event) {
            return $this->defaultSender;
        }

        if (1 === preg_match('/' . $this->applicationDomain . '/', $event->getDomain())) {
            return self::MAIL . '@' . $event->getDomain();
        }

        return $this->defaultSender;
    }
}
