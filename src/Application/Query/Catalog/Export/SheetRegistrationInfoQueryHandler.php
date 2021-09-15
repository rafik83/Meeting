<?php

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class SheetRegistrationInfoQueryHandler
{
    const TRANS_GENDER = 'gender.%s';
    const TRANS_BOOLEAN = 'boolean.%s';

    /** @var TranslatorInterface */
    private $translator;

    /** @var array of sheet registration fields key => label */
    private $sheetRegistrationFields = [];

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param SheetRegistrationInfoQuery $query
     *
     * @return array of object key => content
     */
    public function handle(SheetRegistrationInfoQuery $query): array
    {
        $data = [];

        /** @var TemplateObject|TemplateObject\ExportableObjectInterface $object */
        foreach ($query->templateData->getExportableObjects() as $object) {
            if (!$object->hasTag(Tag::SHEET_DATA)) {
                continue;
            }

            $key = $object->getKey();

            if (!isset($this->sheetRegistrationFields[$key])) {
                $fieldName = $object->getExportableFieldname($query->locale, $query->fallback);
                $this->sheetRegistrationFields[$key] = $fieldName;
            }

            $content = $object->getExportableContent([], $query->locale);

            if ($object instanceof TemplateObject\Gender) {
                $content = $this->translator->trans(
                    sprintf(self::TRANS_GENDER, $content),
                    [],
                    'exports',
                    $query->locale
                );
            } elseif ($object instanceof TemplateObject\BooleanObject) {
                $content = $this->translator->trans(
                    sprintf(self::TRANS_BOOLEAN, $content ? 'yes' : 'no'),
                    [],
                    'exports',
                    $query->locale
                );
            }

            $data[$key] = $content;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getSheetRegistrationFields(): array
    {
        return $this->sheetRegistrationFields;
    }
}
