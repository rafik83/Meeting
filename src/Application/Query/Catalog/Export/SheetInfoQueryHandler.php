<?php

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Domain\Template\TemplateObject\TagsCollection;

class SheetInfoQueryHandler
{
    /** @var array of sheet fields key => label */
    private $sheetFields = [];

    /**
     * @param SheetInfoQuery $query
     *
     * @return array of object key => content
     */
    public function handle(SheetInfoQuery $query): array
    {
        $data = [];

        foreach ($query->templateData->getExportableObjects() as $object) {
            // We do not export tag, as they are already given with the registration data
            if ($object instanceof TagsCollection) {
                continue;
            }

            $key = $object->getKey();

            if (!isset($this->sheetFields[$key])) {
                $fieldName = $object->getExportableFieldname($query->locale, $query->fallback);
                $this->sheetFields[$key] = $fieldName;
            }

            $data[$key] = $object->getExportableContent($query->taggedData, $query->locale);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getSheetFields(): array
    {
        return $this->sheetFields;
    }
}
