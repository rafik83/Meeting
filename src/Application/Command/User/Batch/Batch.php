<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;

class Batch implements Command
{
    public const SELECTION_TYPE_PAGE = 'selection_type_page';
    public const SELECTION_TYPE_ALL  = 'selection_type_all';

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var array */
    public $ids;

    /** @var Admin */
    public $admin;

    /** @var bool */
    public $isCampaignCreation;

    /** @var bool */
    public $isExportFormTemplate;

    /** @var string */
    public $campaignTitle;

    /** @var FormTemplate|null */
    public $formTemplate;

    /** @var string */
    public $selectionType;

    /** @var null|Condition */
    public $condition;

    public function __construct(Event $event, Admin $admin, string $locale, ?Condition $condition = null)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->condition = $condition;
        $this->admin = $admin;
    }
}
