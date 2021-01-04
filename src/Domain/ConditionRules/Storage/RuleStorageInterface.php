<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Storage;

use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Event;

interface RuleStorageInterface
{
    public function getRulesQuery(Event $event, string $type): ?string;

    public function getRules(Event $event, string $locale, string $type): ?RuleInterface;

    public function saveRules(Event $event, string $type, string $rules): void;

    public function removeRules(Event $event, string $type): void;
}
