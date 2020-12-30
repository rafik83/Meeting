<?php

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;

class TemplatePreviewResolver
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $sheetTemplateRepository;

    /**
     * TemplatePreviewResolver constructor.
     *
     * @param TemplateDataFactory              $templateDataFactory
     * @param SheetTemplateRepositoryInterface $sheetTemplateRepository
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        SheetTemplateRepositoryInterface $sheetTemplateRepository
    ) {
        $this->templateDataFactory     = $templateDataFactory;
        $this->sheetTemplateRepository = $sheetTemplateRepository;
    }

    /**
     * Remove preview object if their not in the event sheet template
     *
     * @param SheetTemplate $sheetTemplate
     */
    public function resolve(SheetTemplate $sheetTemplate)
    {
        $templateData = $this->templateDataFactory->createFromTemplate($sheetTemplate);
        $previews     = $sheetTemplate->getPreview();

        foreach ($previews as $key => $objectKey) {
            try {
                $templateData->getObject($objectKey);
            } catch (ObjectNotFoundException $exception) {
                unset($previews[$key]); // remove preview object
            }
        }

        $this->sheetTemplateRepository->set($sheetTemplate->setPreview($previews));
    }
}
