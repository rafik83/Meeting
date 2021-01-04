<?php

namespace Proximum\Vimeet\Application\Components\Security;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class AdminSheetAccess
{
    /**
     * @param Admin $admin
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function canAccess(Admin $admin, Sheet $sheet)
    {
        if ($admin->hasAllowedTypes()) {
            return in_array($sheet->getType(), $admin->getAllowedTypes());
        }

        return true;
    }
}
