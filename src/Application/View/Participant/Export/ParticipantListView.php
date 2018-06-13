<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Participant\Export;

class ParticipantListView
{
    /** @var string */
    public $locale;

    /** @var ParticipantView[] */
    public $participantViews;

    /** @var array of key => label of the registration fields */
    public $registrationColumns;

    /** @var string[] */
    public $productColumns;

    /**
     * @param string            $locale
     * @param ParticipantView[] $participantViews
     * @param array             $registrationColumns
     * @param string[]          $productColumns
     */
    public function __construct(
        string $locale,
        array $participantViews,
        array $registrationColumns,
        array $productColumns
    ) {
        $this->participantViews = $participantViews;
        $this->registrationColumns = $registrationColumns;
        $this->locale = $locale;
        $this->productColumns = $productColumns;
    }
}
