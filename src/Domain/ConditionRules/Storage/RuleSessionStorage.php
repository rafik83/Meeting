<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Storage;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Query\ConditionRules\Rules\GetConditionRulesQuery;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Event;

class RuleSessionStorage implements RuleStorageInterface
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var SessionInterface */
    private $session;

    public function __construct(QueryBusInterface $queryBus, SessionInterface $session)
    {
        $this->queryBus = $queryBus;
        $this->session = $session;
    }

    public function getRulesQuery(Event $event, string $type): ?string
    {
        return $this->session->get(sprintf('%s_rules_%d', $type, $event->getId()));
    }

    public function getRules(Event $event, string $locale, string $type): ?RuleInterface
    {
        $rules = json_decode($this->getRulesQuery($event, $type), true);

        if ($rules) {
            return $this->queryBus->handle(new GetConditionRulesQuery($event, $locale, $rules));
        }

        return null;
    }

    public function saveRules(Event $event, string $type, string $rules): void
    {
        $this->session->set($this->getRulesKey($event, $type), $rules);
    }

    public function removeRules(Event $event, string $type): void
    {
        $this->session->remove($this->getRulesKey($event, $type));
    }

    private function getRulesKey(Event $event, string $type): string
    {
        return sprintf('%s_rules_%d', $type, $event->getId());
    }
}
