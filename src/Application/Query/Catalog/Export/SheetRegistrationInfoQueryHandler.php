<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Application\Adapter\IntlInterface;
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

    /** @var IntlInterface */
    private $intl;

    /**
     * @param TranslatorInterface $translator
     * @param IntlInterface       $intl
     */
    public function __construct(TranslatorInterface $translator, IntlInterface $intl)
    {
        $this->translator = $translator;
        $this->intl = $intl;
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
            $fieldName = $object->getExportableFieldname($query->locale, $query->fallback);

            if (!isset($this->sheetRegistrationFields[$key])) {
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
            } elseif ($object instanceof TemplateObject\Country) {
                $content = $this->intl->getCountryName($content, $query->locale);
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
