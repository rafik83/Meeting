<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Export;

use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Event;

class ExportQuery
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var string */
    public $locale;

    /** @var null|RuleInterface */
    public $condition;

    /** @var string */
    public $charset;

    /** @var bool */
    public $displayNomenclatureIds;

    public function __construct(
        Event $event,
        array $filters,
        string $locale,
        bool $displayNomenclatureIds = false,
        ?RuleInterface $condition = null,
        string $charset = Charset::WINDOWS_1252
    ) {
        $this->event = $event;
        $this->filters = $filters;
        $this->locale  = $locale;
        $this->condition = $condition;
        $this->charset = $charset;
        $this->displayNomenclatureIds = $displayNomenclatureIds;
    }
}
