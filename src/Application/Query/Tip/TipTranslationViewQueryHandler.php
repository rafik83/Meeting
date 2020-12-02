<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\Query\Tip\Condition\ConditionInterface;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipTranslationViewQueryHandler
{
    const CONTEXT_CATALOG = 'event_catalog_index';
    const CONTEXT_MEETING_MANAGEMENT = 'event_meeting_list_request';
    const CONTEXT_PRINT_PLANNING = 'print_planning';
    const CONTEXT_SHEET = 'onSheet';
    const CONTEXT_AGENDA = 'onAgenda';
    const CONTEXT_PACKAGE = 'onPackage';
    const CONTEXT_CONTACTS = 'onContacts';
    const CONTEXT_PROGRAM = 'onProgram';
    const CONTEXT_CONFIRMATION_PHONE = 'onConfirmationPhone';
    const CONTEXT_NETWORKING = 'onNetworking';

    /**
     * keys are context, value are table fields
     *
     * @var array
     */
    private static $contextsMapping = [
        self::CONTEXT_CATALOG            => 'onCatalog',
        self::CONTEXT_MEETING_MANAGEMENT => 'onMeetingManagement',
        self::CONTEXT_PRINT_PLANNING     => 'onPrintPlanning',
        self::CONTEXT_SHEET              => 'onSheet',
        self::CONTEXT_AGENDA             => 'onAgenda',
        self::CONTEXT_PACKAGE            => 'onPackage',
        self::CONTEXT_CONTACTS           => 'onContacts',
        self::CONTEXT_PROGRAM            => 'onProgram',
        self::CONTEXT_CONFIRMATION_PHONE => 'onConfirmationPhone',
        self::CONTEXT_NETWORKING         => 'onNetworking',
    ];

    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var ConditionInterface[] */
    private $conditions;

    /** @var IsTipOpened */
    private $isTipOpened;

    /**
     * @param TipRepositoryInterface $tipRepository
     * @param IsTipOpened            $isTipOpened
     * @param ConditionInterface[]   $conditions
     */
    public function __construct(TipRepositoryInterface $tipRepository, IsTipOpened $isTipOpened, array $conditions)
    {
        $this->tipRepository = $tipRepository;
        $this->isTipOpened = $isTipOpened;
        $this->conditions = $conditions;
    }

    /**
     * @param TipTranslationViewQuery $query
     *
     * @return TipTranslationView[]
     */
    public function handle(TipTranslationViewQuery $query)
    {
        if (!isset(self::$contextsMapping[$query->context])) {
            return null;
        }

        $tipTranslationViews = $this->tipRepository->getByContextAndEventAndType(
            $query->event,
            $query->type,
            self::$contextsMapping[$query->context],
            $query->locale
        );

        $tipTranslationListView = [];

        foreach ($tipTranslationViews as $tipTranslationView) {
            foreach ($this->conditions as $condition) {
                if (!$condition->isSatisfiedBy($query, $tipTranslationView)) {
                    continue 2;
                }
            }

            $tipTranslationView->isOpened = $this->isTipOpened->isSatisfiedBy($query, $tipTranslationView);
            $tipTranslationView->setImage($query->event->getLocalizedNotificationImage($query->locale));
            $tipTranslationListView[] = $tipTranslationView;
        }

        return $tipTranslationListView;
    }
}
