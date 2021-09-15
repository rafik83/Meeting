<?php

namespace Proximum\Vimeet\Application\Command\Participant\Export;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class PrepareExport implements Command
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    /** @var null|RuleInterface */
    public $condition;

    public function __construct(Event $event, array $filters, Admin $admin, string $locale, ?RuleInterface $condition = null)
    {
        $this->event = $event;
        $this->filters = $filters;
        $this->admin = $admin;
        $this->locale = $locale;
        $this->condition = $condition;
    }
}
