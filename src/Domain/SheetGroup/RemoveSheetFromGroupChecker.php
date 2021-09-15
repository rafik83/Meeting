<?php

namespace Proximum\Vimeet\Domain\SheetGroup;

use Proximum\Vimeet\Domain\Model\Sheet;

class RemoveSheetFromGroupChecker
{
    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function canRemoveSheetFromGroup(Sheet $sheet)
    {
        if (null === $sheet->getGroup()) {
            throw new \InvalidArgumentException('Sheet not have group');
        }

        return $this->ownerOrUserInSheetIsAlsoTheGroupManager($sheet);
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    private function ownerOrUserInSheetIsAlsoTheGroupManager(Sheet $sheet)
    {
        $groupManager = $sheet->getGroup()->getManager();

        foreach ($sheet->getUsers() as $user) {
            if ($groupManager->getId() === $user->getId()) {
                return false;
            }
        }

        return true;
    }
}
