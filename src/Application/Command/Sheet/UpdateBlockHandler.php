<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Components\Template\Template;
use Proximum\Vimeet\Application\Components\Template\TemplateFactory;
use Proximum\Vimeet\Application\Components\Template\Validator;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateBlockHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * UpdateBlockHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param TemplateFactory          $templateFactory
     * @param Validator                $validator
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, TemplateFactory $templateFactory, Validator $validator)
    {
        $this->sheetRepository = $sheetRepository;
        $this->templateFactory = $templateFactory;
        $this->validator       = $validator;
    }

    /**
     * @param UpdateBlock $updateBlock
     *
     * @throws RequiredDataEmptyException
     */
    public function handle(UpdateBlock $updateBlock)
    {
        // Get block template
        $array    = $updateBlock->sheet->getType()->getSheetTemplate();
        $template = $this->templateFactory->createTemplatesFromArray($array)->getTemplate($updateBlock->block);

        // Validate block data against the template
        $this->validator->validateDataAgainstTemplate($updateBlock->data, $template);

        // Update data
        $data = $updateBlock->sheet->getData();

        $data[$updateBlock->block] = $this->merge(
            $template,
            isset($data[$updateBlock->block]) ? $data[$updateBlock->block] : [],
            $updateBlock->data,
            $updateBlock->locale
        );

        $updateBlock->sheet->setData($data);

        $this->sheetRepository->set($updateBlock->sheet);
    }

    /**
     * @param Template $template
     * @param array    $old
     * @param array    $new
     * @param string   $locale
     *
     * @return array
     */
    private function merge(Template $template, array $old, array $new, $locale)
    {
        foreach ($new as $key => $value) {
            $translatable = $template->getRow($key)->isTranslatable();

            $old[$key] = $translatable ? array_merge(isset($old[$key]) ? $old[$key] : [], [$locale => $value]) : $value;
        }

        return $old;
    }
}
