<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class Batch extends AbstractBatch
{
    const SELECTION_TYPE_PAGE = 'selection_type_page';
    const SELECTION_TYPE_ALL  = 'selection_type_all';

    /**
     * @var array
     */
    public $ids;

    /**
     * @var bool
     */
    public $validate;

    /**
     * @var bool
     */
    public $accept;

    /**
     * @var bool
     */
    public $assign;

    /**
     * @var bool
     */
    public $enable;

    /**
     * @var bool
     */
    public $disable;

    /**
     * @var Admin
     */
    public $follower;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var string
     */
    public $validateComment;

    /**
     * @var bool
     */
    public $addCatalog;

    /**
     * @var bool
     */
    public $removeCatalog;

    /**
     * @var bool
     */
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

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $selectionType;

    /**
     * "Générer facture"
     *
     * @var bool
     */
    public $generateInvoice;

    /**
     * @param Event  $event
     * @param Admin $admin
     * @param Admin  $admin
     * @param string $locale
     * @param array  $filters
     */
    public function __construct(Event $event, Admin $admin, $locale, $filters = [])
    {
        $this->admin   = $admin;
        $this->filters = $filters;
        $this->event   = $event;
        $this->locale  = $locale;
    }
}
