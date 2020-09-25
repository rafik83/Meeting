<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Export;

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

    /** @var string */
    public $locale;

    /** @var null|RuleInterface */
    public $condition;

    /** @var Admin */
    public $admin;

    /** @var bool */
    public $displayNomenclatureIds;

    public function __construct(
        Event $event,
        array $filters,
        string $locale,
        Admin $admin,
        bool $displayNomenclatureIds = false,
        ?RuleInterface $condition = null
    ) {
        $this->event = $event;
        $this->filters = $filters;
        $this->locale  = $locale;
        $this->condition = $condition;
        $this->admin = $admin;
        $this->displayNomenclatureIds = $displayNomenclatureIds;
    }
}
