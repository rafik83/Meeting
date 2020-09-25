<?php

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\View\Template\TaggedDataView;

class TaggedDataFactory
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var TaggedDataView[] */
    private $taggedDataViews = [];

    /** @var Applyer */
    private $applyer;

    /** @var PrintTemplateResolver */
    private $printTemplateResolver;

    /** @var TrackingUrlTransformer */
    private $trackingUrlTransformer;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        PrintTemplateResolver $printTemplateResolver,
        Applyer $applyer,
        TrackingUrlTransformer $trackingUrlTransformer
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->applyer = $applyer;
        $this->printTemplateResolver = $printTemplateResolver;
        $this->trackingUrlTransformer = $trackingUrlTransformer;
    }

    /**
     * Get tagged data from registration template and build TaggedDataView into sheetTemplate
     *
     * @param Sheet  $sheet
     * @param string $locale
     * @param array  $rules  Current user rules
     *
     * @see RuleRepositoryInterface::getBySeerTypeAndSeeableType
     *
     * @return TemplateData sheetTemplate
     */
    public function buildTaggedDataView(Sheet $sheet, $locale, array $rules = []): TemplateData
    {
        $this->createTaggedDataView($sheet, $locale);

        return $this->attachTaggedDataView($sheet, $locale, $rules);
    }

    /**
     * Get tagged data from registration template and build TaggedDataView into printTemplate
     *
     * @param Sheet  $sheet
     * @param string $locale
     * @param array  $rules  Current user rules
     *
     * @see RuleRepositoryInterface::getBySeerTypeAndSeeableType
     *
     * @return TemplateData sheetTemplate
     */
    public function buildTaggedDataViewForPrint(Sheet $sheet, string $locale, array $rules = [])
    {
        $this->createTaggedDataView($sheet, $locale);
        $printTemplateData = $this->attachTaggedDataViewOnPrintTemplate($sheet, $locale, $rules);

        return $printTemplateData;
    }

    /**
     * Build taggedDataView for all registration template objects
     *
     * @see TaggedDataView
     */
    private function createTaggedDataView(Sheet $sheet, string $locale)
    {
        $registerTemplateData = $this->templateDataFactory->createRegistrationFromSheet($sheet, $locale);

        $objects      = $registerTemplateData->getContentObjects();
        $eventLocales = $sheet->getEvent()->getLocales();

        /** @var TemplateObject|ContentObjectInterface $object */
        foreach ($objects as $object) {
            $tags = $object->getTags();

            if (0 === \count($tags)) {
                continue;
            }

            // Filter only the object with SHEET_DATA setter,
            // as they are the only one that can be display on the sheet
            if (!\in_array(Tag::SHEET_DATA, $tags, true)) {
                continue;
            }

            foreach ($tags as $tag) {
                if (\in_array($tag, Tag::getSetters(), true)) {
                    continue;
                }

                // No use to do all this as we will not attached the taggedDataViews
                // if it already exists for this tag.
                if (isset($this->taggedDataViews[$sheet->getId()][$tag])) {
                    continue;
                }

                $originalUrl = null;

                if ($object instanceof TemplateObject\Nomenclature) {
                    $value = implode(', ', $object->getNomenclatureLabelOfItems());
                } elseif ($object instanceof TemplateObject\DateTime) {
                    if (!$object->getDatetime() instanceof \DateTime) {
                        $value = '';
                    } else {
                        $value = $object->getFormattedDate($locale);
                    }
                } elseif ($object instanceof TemplateObject\Url) {
                    $originalUrl = $object->getUrl();
                    $value = $this->trackingUrlTransformer->transform($sheet, $object);
                } else {
                    $value = $object->getContentValueLocalize($locale);
                }

                $taggedDataView = new TaggedDataView(
                    $object->getType(),
                    $object->isTranslatable(),
                    $object instanceof TranslatableInterface ? $object->getTranslations($eventLocales) : [],
                    $value,
                    $tag,
                    $object instanceof EditableText ? $object->isTextarea() : false,
                    $originalUrl
                );

                $this->addTaggedDataView($sheet, $tag, $taggedDataView);
            }
        }
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param array  $rules
     *
     * @return TemplateData
     */
    private function attachTaggedDataView(Sheet $sheet, $locale, array $rules = []): TemplateData
    {
        $sheetTemplateData = $this->templateDataFactory->createFromSheet($sheet, $locale);

        return $this->attachTaggedData($sheetTemplateData, $sheet, $rules);
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param array  $rules
     *
     * @return TemplateData
     */
    private function attachTaggedDataViewOnPrintTemplate(Sheet $sheet, string $locale, array $rules = []): TemplateData
    {
        $sheetTemplateData = $this->printTemplateResolver->resolvePrintTemplate($sheet, $locale);

        return $this->attachTaggedData($sheetTemplateData, $sheet, $rules);
    }

    /**
     * @param TemplateData $templateData
     * @param Sheet        $sheet
     * @param array        $rules
     *
     * @see RuleRepositoryInterface::getBySeerTypeAndSeeableType
     *
     * @return TemplateData
     */
    private function attachTaggedData(TemplateData $templateData, Sheet $sheet, array $rules): TemplateData
    {
        if (!empty($rules)) {
            $this->applyer->applyRuleForTemplate($templateData, $rules);
        }

        $tags = [];
        if (isset($this->taggedDataViews[$sheet->getId()])) {
            $tags = $this->taggedDataViews[$sheet->getId()];
        }

        $templateData->setTaggedDataViews($tags);

        return $templateData;
    }

    /**
     * @param Sheet          $sheet
     * @param string         $tag
     * @param TaggedDataView $taggedDataView
     */
    private function addTaggedDataView(Sheet $sheet, $tag, $taggedDataView): void
    {
        if (!isset($this->taggedDataViews[$sheet->getId()][$tag])) {
            $this->taggedDataViews[$sheet->getId()][$tag] = $taggedDataView;
        }
    }
}
