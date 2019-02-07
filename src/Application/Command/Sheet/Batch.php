<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

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

    /** @var Type */
    public $duplicateToType;

    /**
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     * @param array  $filters
     */
    public function __construct(Event $event, Admin $admin, $locale, array $filters = [])
    {
        $this->admin   = $admin;
        $this->filters = $filters;
        $this->event   = $event;
        $this->locale  = $locale;
    }
}
