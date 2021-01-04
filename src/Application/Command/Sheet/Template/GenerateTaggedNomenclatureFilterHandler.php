<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;
use Proximum\Vimeet\Domain\Repository\Filter\TaggedNomenclatureFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class GenerateTaggedNomenclatureFilterHandler
{
    /** @var SheetTemplateRepositoryInterface */
    private $sheetTemplateRepository;

    /** @var TaggedNomenclatureFilterRepositoryInterface */
    private $taggedNomenclatureFilterRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        TaggedNomenclatureFilterRepositoryInterface $taggedNomenclatureFilterRepository,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->sheetTemplateRepository = $sheetTemplateRepository;
        $this->taggedNomenclatureFilterRepository = $taggedNomenclatureFilterRepository;
        $this->templateDataFactory = $templateDataFactory;
    }

    public function handle(GenerateTaggedNomenclatureFilter $generateTaggedNomenclatureFilter): void
    {
        $event = $generateTaggedNomenclatureFilter->event;

        if (null === $event) {
            return;
        }

        $tags = Tag::getGenericSheetTemplateTags();
        $templatesData = $this->getTemplateDataByEvent($event);
        $this->taggedNomenclatureFilterRepository->deleteForEventAndTags($event, $tags);

        foreach ($tags as $tag) {
            $nomenclaturesAdded = $this->getNomenclaturesForGivenTag($templatesData, $tag);

            if (\count($nomenclaturesAdded)) {
                $this->taggedNomenclatureFilterRepository->add(
                    new TaggedNomenclatureFilter($event, $tag, $nomenclaturesAdded)
                );
            }
        }
    }

    /**
     * @return TemplateData[]
     */
    private function getTemplateDataByEvent(Event $event): array
    {
        $templates = $this->sheetTemplateRepository->getUsedTemplateForGivenEvent($event);

        $templatesData = [];

        foreach ($templates as $template) {
            $templatesData[] = $this->templateDataFactory->createFromTemplate($template);
        }

        return $templatesData;
    }

    /**
     * @param TemplateData[] $templatesData
     */
    private function getNomenclaturesForGivenTag(array &$templatesData, string $tag): array
    {
        $nomenclaturesAdded = [];

        foreach ($templatesData as $templateData) {
            foreach ($templateData->getObjects() as $templateObject) {
                if ($templateObject instanceof Nomenclature
                    && $templateObject->hasTag($tag)
                ) {
                    $nomenclatureId = $templateObject->getNomenclatureId();

                    if (!isset($nomenclaturesAdded[$nomenclatureId])) {
                        $nomenclaturesAdded[$nomenclatureId] = $nomenclatureId;
                    }
                }
            }
        }

        return $nomenclaturesAdded;
    }
}
