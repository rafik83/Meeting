<?php

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Rule\Composer;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ExportableObjectInterface;

class SheetViewQueryHandler
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var CategoryNameResolver */
    private $categoryNameResolver;

    /** @var Applyer */
    private $applyer;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var Composer */
    private $composer;

    /** @var SheetInfoQueryHandler */
    private $sheetInfoQueryHandler;

    /** @var SheetRegistrationInfoQueryHandler */
    private $sheetRegistrationInfoQueryHandler;

    /** @var CanSeeSheet */
    private $canSeeSheet;

    /**
     * @param TemplateDataFactory               $templateDataFactory
     * @param ParticipantInfoGuesser            $participantInfoGuesser
     * @param CategoryNameResolver              $categoryNameResolver
     * @param RuleRepositoryInterface           $ruleRepository
     * @param Composer                          $composer
     * @param Applyer                           $applyer
     * @param SheetInfoQueryHandler             $sheetInfoQueryHandler
     * @param SheetRegistrationInfoQueryHandler $sheetRegistrationInfoQueryHandler
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        ParticipantInfoGuesser $participantInfoGuesser,
        CategoryNameResolver $categoryNameResolver,
        RuleRepositoryInterface $ruleRepository,
        Composer $composer,
        Applyer $applyer,
        SheetInfoQueryHandler $sheetInfoQueryHandler,
        SheetRegistrationInfoQueryHandler $sheetRegistrationInfoQueryHandler,
        CanSeeSheet $canSeeSheet
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->categoryNameResolver = $categoryNameResolver;
        $this->applyer = $applyer;
        $this->ruleRepository = $ruleRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->composer = $composer;
        $this->sheetInfoQueryHandler = $sheetInfoQueryHandler;
        $this->sheetRegistrationInfoQueryHandler = $sheetRegistrationInfoQueryHandler;
        $this->canSeeSheet = $canSeeSheet;
    }

    /**
     * @param SheetViewQuery $query
     *
     * @throws \DomainException
     *
     * @return SheetView
     */
    public function handle(SheetViewQuery $query): SheetView
    {
        $sheets = [$query->sheet];
        $locale = $query->locale;

        // This should not happen as the sheet return are the only visible by the viewer
        if (false === $this->canSeeSheet->isSatisfiedBy($query->viewer, $query->sheet)) {
            throw new \DomainException('Sheet not visible');
        }

        $rules = $this->ruleRepository->getBySeerSheetAndSeeableSheet($query->viewer, $query->sheet);

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromSheet($query->sheet, $query->locale);
        $template = $this->templateDataFactory->createFromSheet($query->sheet, $query->locale);
        $tagOfTemplate = $template->getTags();

        $this->purgeRegistrationFromObjectNotShownOnSheet($registrationTemplate, $tagOfTemplate);

        // Clean the template data with the who see what rule
        $this->applyer->applyRuleForRegistrationTemplate($registrationTemplate, $rules);
        $this->applyer->applyRuleForTemplate($template, $rules);

        $participantsPosition = '';

        $rule = $this->composer->compose($rules);

        // The participant position is return only if the viewer has the right to see the participant position tag on the viewed
        if ($rule->isPresent(Tag::PARTICIPANT_POSITION)) {
            $participantsPosition = implode(', ', array_map(function (Participant $participant) use ($locale) {
                return $this->participantInfoGuesser->guessParticipantPositionLabel($participant, $locale);
            }, $query->sheet->getParticipantsArray()));
        }

        if ($query->isTypeColumn) {
            $typeTitle = $query->sheet->getType()->getTitle($query->locale);
        } else {
            $typeTitle = $this->categoryNameResolver->resolveForPreloadSheets($sheets, $query->locale);
        }

        return new SheetView(
            $typeTitle,
            $this->sheetRegistrationInfoQueryHandler->handle(
                new SheetRegistrationInfoQuery($registrationTemplate, $locale, $query->fallback)
            ),
            $this->sheetInfoQueryHandler->handle(
                new SheetInfoQuery($template, $registrationTemplate->getAllTaggedDatas(), $locale, $query->fallback)
            ),
            $participantsPosition
        );
    }

    /**
     * @param SheetView $sheetView
     */
    public function reMapFields(SheetView $sheetView): void
    {
        $sheetRegistrationFields = $this->getSheetRegistrationFields();
        $sheetFields = $this->getSheetFields();

        $sheetView->reMapRegistrationFields($sheetRegistrationFields);
        $sheetView->reMapFields($sheetFields);
    }

    /**
     * @return array
     */
    public function getSheetFields(): array
    {
        return $this->sheetInfoQueryHandler->getSheetFields();
    }

    /**
     * @return array
     */
    public function getSheetRegistrationFields(): array
    {
        return $this->sheetRegistrationInfoQueryHandler->getSheetRegistrationFields();
    }

    /**
     * @param TemplateData $registrationTemplate
     * @param array        $tagOfTemplate
     */
    private function purgeRegistrationFromObjectNotShownOnSheet(
        TemplateData &$registrationTemplate,
        array &$tagOfTemplate
    ): void {
        /** @var ExportableObjectInterface|TemplateObject $object */
        foreach ($registrationTemplate->getExportableObjects() as $object) {
            $found = false;

            foreach ($object->getTags() as $tagInfo) {
                if ((!is_array($tagInfo) && isset($tagOfTemplate[$tagInfo]))
                    || (is_array($tagInfo) && isset($tagInfo['tag']) && isset($tagOfTemplate[$tagInfo['tag']]))
                ) {
                    $found = true;

                    break;
                }
            }

            if (!$found) {
                $registrationTemplate->removeObject($object->getKey());
            }
        }
    }
}
