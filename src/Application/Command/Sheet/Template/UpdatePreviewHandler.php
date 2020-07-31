<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Components\Sheet\Preview\CustomPreviewData;
use Proximum\Vimeet\Application\Exception\Sheet\Template\TemplateException;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\NonUniquePreviewObjectsException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class UpdatePreviewHandler
{
    /** @var SheetTemplateRepositoryInterface */
    private $sheetTemplateRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
        $this->templateDataFactory     = $templateDataFactory;
    }

    /**
     * @param UpdatePreview $updatePreview
     *
     * @throws TemplateException
     */
    public function handle(UpdatePreview $updatePreview): void
    {
        $templateData = $this->templateDataFactory->createFromTemplate($updatePreview->sheetTemplate);

        // check unique
        $uniqueObjects = array_unique($updatePreview->previewObjects);
        if (count($uniqueObjects) !== count($updatePreview->previewObjects)) {
            throw new NonUniquePreviewObjectsException('flash.sheet.template.preview.non_unique_exception');
        }

        // check exist in template
        foreach ($updatePreview->previewObjects as $key) {
            $customPreviewDataView = CustomPreviewData::getCustomPreviewDataViewByName($key);

            if (null !== $customPreviewDataView) {
                continue;
            }

            if (false === $templateData->hasObject($key)) {
                throw new ObjectNotFoundException($key);
            }
        }

        // persist preview objects
        $updatePreview->sheetTemplate->setPreview($updatePreview->previewObjects);
        $this->sheetTemplateRepository->set($updatePreview->sheetTemplate);
    }
}
