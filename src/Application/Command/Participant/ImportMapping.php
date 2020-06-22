<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping as SheetImportMapping;
use Proximum\Vimeet\Domain\Model\Type;

class ImportMapping implements Command
{
    /**
     * A mapping array of csv headers keys and their registration headers key
     *
     * @var array
     */
    private $mappings = [];

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var Type */
    public $type;

    /** @var Admin */
    public $admin;

    /** @var ImportMappingView */
    public $importMappingView;

    public function __construct(
        Event $event,
        Type $type,
        Admin $admin,
        string $locale,
        ImportMappingView $importMappingView,
        ?SheetImportMapping $sheetImportMapping
    ) {
        $this->event = $event;
        $this->type = $type;
        $this->locale = $locale;
        $this->admin = $admin;
        $this->importMappingView = $importMappingView;

        if ($sheetImportMapping instanceof SheetImportMapping) {
            $map = $sheetImportMapping->getMapping();
            $associatedMapping = [];

            $csvHeaders = $this->importMappingView->fieldHeaders;
            $csvHeadersFlip = array_flip($csvHeaders);

            foreach ($map as $key => $value) {
                if (isset($csvHeadersFlip[$key])) {
                    $associatedMapping[$csvHeadersFlip[$key]] = $value;
                }
            }

            $this->mappings = $associatedMapping;
        }
    }

    public function isEmailInMappings(): bool
    {
        return in_array(ParticipantImportTag::REGISTRATION_FIELD_MAIL, $this->mappings, true);
    }

    public function isGroupTitleInMappingsForAllowMultiSheet(): bool
    {
        if (true === $this->importMappingView->allowMultiSheet) {
            return in_array(ParticipantImportTag::FIELD_GROUP_TITLE, $this->mappings, true);
        }

        return true;
    }

    public function isOnlyOneEmailMapping(): bool
    {
        if ($this->isEmailInMappings()) {
            $mappingsValuesCount = array_count_values($this->mappings);

            return $mappingsValuesCount[ParticipantImportTag::REGISTRATION_FIELD_MAIL] <= 1;
        }

        return true;
    }

    public function setMappings(array $mappingIndexedByInt): void
    {
        $mappingIndexedByFileHeader = [];

        foreach ($mappingIndexedByInt as $key => $field) {
            if (isset($this->importMappingView->fieldHeaders[$key])) {
                $mappingIndexedByFileHeader[$this->importMappingView->fieldHeaders[$key]] = $field;
            }
        }

        $this->mappings = $mappingIndexedByFileHeader;
    }

    public function getMappings(): array
    {
        return $this->mappings;
    }
}
