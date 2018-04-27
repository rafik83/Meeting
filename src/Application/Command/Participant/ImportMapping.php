<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class ImportMapping
{
    /**
     * A mapping array of csv headers keys and their registration headers key
     *
     * @var array
     */
    private $mappings = [];

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Type
     */
    public $type;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var ImportMappingView
     */
    public $importMappingView;

    /**
     * ImportMapping constructor.
     *
     * @param Event             $event
     * @param Type              $type
     * @param Admin             $admin
     * @param string            $locale
     * @param ImportMappingView $importMappingView
     */
    public function __construct(
        Event $event,
        Type $type,
        Admin $admin,
        $locale,
        ImportMappingView $importMappingView
    ) {
        $this->event             = $event;
        $this->type              = $type;
        $this->locale            = $locale;
        $this->admin             = $admin;
        $this->importMappingView = $importMappingView;
    }

    /**
     * @return bool
     */
    public function isEmailInMappings()
    {
        return in_array(ParticipantImportTag::REGISTRATION_FIELD_MAIL, $this->mappings, true);
    }

    /**
     * @return bool
     */
    public function isOnlyOneEmailMapping()
    {
        if ($this->isEmailInMappings()) {
            $mappingsValuesCount = array_count_values($this->mappings);

            return $mappingsValuesCount[ParticipantImportTag::REGISTRATION_FIELD_MAIL] <= 1;
        } else {
            return false;
        }
    }

    /**
     * @param array $mappingIndexedByInt
     */
    public function setMappings(array $mappingIndexedByInt)
    {
        $mappingIndexedByFileHeader = [];

        foreach ($mappingIndexedByInt as $key => $field) {
            if (isset($this->importMappingView->fieldHeaders[$key])) {
                $mappingIndexedByFileHeader[$this->importMappingView->fieldHeaders[$key]] = $field;
            }
        }

        $this->mappings = $mappingIndexedByFileHeader;
    }

    /**
     * @return array
     */
    public function getMappings(): array
    {
        return $this->mappings;
    }
}
