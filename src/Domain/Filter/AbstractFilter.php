<?php

namespace Proximum\Vimeet\Domain\Filter;

use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Domain\Model\Event;

abstract class AbstractFilter
{
    /** @var SessionInterface */
    private $session;

    abstract public function getName(): string;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param Event $event
     *
     * @return array|null
     */
    public function get(Event $event)
    {
        return $this->session->get($this->getKey($event));
    }

    /**
     * @param Event $event
     * @param array $filters
     */
    public function add(Event $event, array $filters)
    {
        $this->session->set($this->getKey($event),
            array_filter($filters, function ($filter) {
                return null !== $filter;
            })
        );
    }

    /**
     * @param Event $event
     */
    public function clear(Event $event)
    {
        $this->session->remove($this->getKey($event));
    }

    /**
     * @param Event $event
     *
     * @return string
     */
    private function getKey(Event $event)
    {
        return sprintf('%s_%s', $this->getName(), $event->getId());
    }
}
