<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Planning\PlanningOrderedBy;

class Batch extends AbstractBatch
{
    const SELECTION_TYPE_PAGE = 'selection_type_page';
    const SELECTION_TYPE_ALL  = 'selection_type_all';

    public const PRINT_OPTION_BADGE = 'printBadge';
    public const PRINT_OPTION_PLANNING_AND_BADGE = 'printPlanningAndBadge';
    public const PRINT_OPTION_PLANNING = 'printPlanning';

    /** @var array */
    public $ids;

    /** @var bool */
    public $validate;

    /** @var bool */
    public $accept;

    /** @var bool */
    public $refuse;

    /** @var bool */
    public $pending;

    /** @var bool */
    public $assign;

    /** @var bool */
    public $enable;

    /** @var bool */
    public $disable;

    /** @var Admin|string|null */
    public $follower;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $validateComment;

    /** @var bool */
    public $addCatalog;

    /** @var bool */
    public $removeCatalog;

    /** @var bool */
    public $draft;

    /**
     * "L'utilisateur a bien completé sa fiche"
     *
     * @var bool
     */
    public $validationValidate;

    /**
     * Sheets list active filters
     *
     * @var array
     */
    public $filters;

    /** @var null|RuleInterface  */
    public $condition;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var string */
    public $selectionType;

    /**
     * "Générer facture"
     *
     * @var bool
     */
    public $generateInvoice;

    /** @var bool */
    public $printInvoices;

    /** @var bool */
    public $printPdf;

    /**
     * "Assigner une fiche à un groupe/entité"
     *
     * @var bool
     */
    public $assignToGroup;

    /** @var Group|null */
    public $group;

    /** @var string */
    public $printPlanningOrderBy = PlanningOrderedBy::ORDER_BY_SHEET_TITLE;

    /** @var string */
    public $printOption;

    /** @var bool */
    public $duplicate = false;

    /** @var Type */
    public $duplicateToType;

    public function __construct(Event $event, Admin $admin, $locale, array $filters = [], ?RuleInterface $condition = null)
    {
        $this->event = $event;
        $this->admin = $admin;
        $this->locale = $locale;
        $this->filters = $filters;
        $this->condition = $condition;
    }
}
