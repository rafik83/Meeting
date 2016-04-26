<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template;

use Proximum\Vimeet\Application\Components\Template\Exception\MissingRequiredDataException;
use Proximum\Vimeet\Domain\Model\Sheet;

class Validator
{
    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * Validator constructor.
     *
     * @param TemplateFactory $templateFactory
     */
    public function __construct(TemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param array     $data
     * @param Templates $templates
     *
     * @throws MissingRequiredDataException
     */
    public function validateDataAgainstTemplates(array $data, Templates $templates)
    {
        foreach ($templates->getTemplates() as $key => $template) {
            $this->validateDataAgainstTemplate(isset($data[$key]) ? $data[$key] : [], $template);
        }
    }

    /**
     * @param array    $data
     * @param Template $template
     *
     * @throws MissingRequiredDataException
     */
    public function validateDataAgainstTemplate(array $data, Template $template)
    {
        $missingRequiredKeys = array_keys(array_filter($template->getRows(), function (Row $row, $key) use ($data) {
            return $row->isRequired() && !isset($data[$key]);
        }, ARRAY_FILTER_USE_BOTH));

        if (!empty($missingRequiredKeys)) {
            throw new MissingRequiredDataException($missingRequiredKeys);
        }
    }

    /**
     * @param Sheet $sheet
     * @param array $data
     *
     * @throws MissingRequiredDataException
     */
    public function validateBillingData(Sheet $sheet, array $data)
    {
        $template = $this->templateFactory->createTemplateFromArray($sheet->getBillingTemplate());

        $this->validateDataAgainstTemplate($data, $template);
    }

    /**
     * @param Sheet $sheet
     * @param array $data
     *
     * @throws MissingRequiredDataException
     */
    public function validateParticipantData(Sheet $sheet, array $data)
    {
        $template = $this->templateFactory->createTemplateFromArray($sheet->getType()->getParticipantTemplate());

        $this->validateDataAgainstTemplate($data, $template);
    }

    /**
     * @param Sheet $sheet
     * @param array $data
     *
     * @throws MissingRequiredDataException
     */
    public function validateSheetData(Sheet $sheet, array $data)
    {
        $templates = $this->templateFactory->createTemplatesFromArray($sheet->getType()->getSheetTemplate());

        $this->validateDataAgainstTemplates($data, $templates);
    }
}
