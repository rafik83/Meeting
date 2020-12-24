<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\SheetGroup\RemoveSheetFromGroupChecker;

class BatchAssignToGroupHandler
{
    const MESSAGE_ASSIGN_SUCCESS                    = 'flash.admin.sheet_batch.assignToGroup.success';
    const MESSAGE_ASSIGN_AND_IGNORED_SHEETS         = 'flash.admin.sheet_batch.assignToGroup.ignoredSheets';
    const MESSAGE_UNASSIGN_SUCCESS                  = 'flash.admin.sheet_batch.unassignFromGroup.success';
    const MESSAGE_UNASSIGN_AND_IGNORED_SHEETS       = 'flash.admin.sheet_batch.unassignFromGroup.ignoredSheets';
    const MESSAGE_UNASSIGN_SHEET_NOT_HAVE_GROUP     = 'flash.admin.sheet_batch.unassignFromGroup.sheetNotHaveGroup';
    const MESSAGE_UNASSIGN_SHEET_CANNOT_BE_REMOVED  = 'flash.admin.sheet_batch.unassignFromGroup.sheetCannotBeRemoved';

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var RemoveSheetFromGroupChecker */
    private $removeSheetFromGroupChecker;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param SheetRepositoryInterface    $sheetRepository
     * @param SheetInfoGuesser            $sheetInfoGuesser
     * @param RemoveSheetFromGroupChecker $removeSheetFromGroupChecker
     * @param TranslatorInterface         $translator
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        RemoveSheetFromGroupChecker $removeSheetFromGroupChecker,
        TranslatorInterface $translator
    ) {
        $this->sheetRepository             = $sheetRepository;
        $this->sheetInfoGuesser            = $sheetInfoGuesser;
        $this->removeSheetFromGroupChecker = $removeSheetFromGroupChecker;
        $this->translator                  = $translator;
    }

    /**
     * @param BatchAssignToGroup $batchAssignToGroup
     *
     * @return BatchResult
     */
    public function handle(BatchAssignToGroup $batchAssignToGroup)
    {
        $locale = $batchAssignToGroup->locale;
        $sheets = $this->sheetRepository->getSheetsById($batchAssignToGroup->ids);

        if ($batchAssignToGroup->group instanceof Sheet\Group) {
            return $this->assign($sheets, $batchAssignToGroup->group, $locale);
        }

        return $this->unassign($sheets, $locale);
    }

    /**
     * @param Sheet[]     $sheets
     * @param Sheet\Group $group
     * @param string      $locale
     *
     * @return BatchResult
     */
    private function assign(array &$sheets, Sheet\Group $group, $locale)
    {
        $sheetsAlreadyWithGroup = [];

        foreach ($sheets as $key => $sheet) {
            if ($sheet->hasGroup()) {
                $sheetsAlreadyWithGroup[$sheet->getId()] = $sheet;

                unset($sheets[$key]);

                continue;
            }

            $sheet->setGroup($group);
            $this->sheetRepository->set($sheet);
        }

        $ignoredSheetsMessage = null;
        $message              = self::MESSAGE_ASSIGN_SUCCESS;

        if (!empty($sheetsAlreadyWithGroup)) {
            $message = self::MESSAGE_ASSIGN_AND_IGNORED_SHEETS;

            $ignoredSheetsMessage = $this->getSheetsTitleList($sheetsAlreadyWithGroup, $locale);
        }

        return new BatchResult($sheets, $message, $ignoredSheetsMessage);
    }

    /**
     * @param Sheet[] $sheets
     * @param string  $locale
     *
     * @return BatchResult
     */
    private function unassign(array &$sheets, $locale)
    {
        $ignoredSheetsWithoutGroup             = [];
        $ignoredSheetsCannotBeRemovedFromGroup = [];

        foreach ($sheets as $key => $sheet) {
            if (!$sheet->hasGroup()) {
                $ignoredSheetsWithoutGroup[] = $sheet;
                unset($sheets[$key]);

                continue;
            }

            if (!$this->removeSheetFromGroupChecker->canRemoveSheetFromGroup($sheet)) {
                $ignoredSheetsCannotBeRemovedFromGroup[] = $sheet;
                unset($sheets[$key]);

                continue;
            }

            $sheet->unassignFromGroup();
            $this->sheetRepository->set($sheet);
        }

        $ignoredSheetsMessage = '';
        $message = self::MESSAGE_UNASSIGN_SUCCESS;

        if (!empty($ignoredSheetsWithoutGroup) || !empty($ignoredSheetsCannotBeRemovedFromGroup)) {
            $message = self::MESSAGE_UNASSIGN_AND_IGNORED_SHEETS;
            $ignoredSheetsMessage = $this->getIgnoredUnassignedSheetsMessage(
                $ignoredSheetsWithoutGroup,
                $ignoredSheetsCannotBeRemovedFromGroup,
                $locale
            );
        }

        return new BatchResult($sheets, $message, $ignoredSheetsMessage);
    }

    /**
     * @param Sheet[] $ignoredSheetsWithoutGroup
     * @param Sheet[] $ignoredSheetsCannotBeRemovedFromGroup
     * @param string  $locale
     *
     * @return string
     */
    private function getIgnoredUnassignedSheetsMessage(
        array &$ignoredSheetsWithoutGroup,
        array &$ignoredSheetsCannotBeRemovedFromGroup,
        $locale
    ) {
        $ignoredSheetsMessageArray = [];

        if (!empty($ignoredSheetsWithoutGroup)) {
            $ignoredSheetsMessageArray[] = $this->translator->transChoice(
                self::MESSAGE_UNASSIGN_SHEET_NOT_HAVE_GROUP,
                count($ignoredSheetsWithoutGroup),
                ['%sheets%' => $this->getSheetsTitleList($ignoredSheetsWithoutGroup, $locale)],
                'flashes'
            );
        }

        if (!empty($ignoredSheetsCannotBeRemovedFromGroup)) {
            $ignoredSheetsMessageArray[] = $this->translator->transChoice(
                self::MESSAGE_UNASSIGN_SHEET_CANNOT_BE_REMOVED,
                count($ignoredSheetsCannotBeRemovedFromGroup),
                ['%sheets%' => $this->getSheetsTitleList($ignoredSheetsCannotBeRemovedFromGroup, $locale)],
                'flashes'
            );
        }

        return implode(', ', $ignoredSheetsMessageArray);
    }

    /**
     * @param Sheet[] $sheets
     * @param string  $locale
     *
     * @return string
     */
    private function getSheetsTitleList(array &$sheets, $locale)
    {
        return implode(', ', array_map(
            function (Sheet $sheet) use ($locale) {
                return $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale);
            },
            $sheets
        ));
    }
}
