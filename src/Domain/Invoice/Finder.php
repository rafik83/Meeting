<?php

namespace Proximum\Vimeet\Domain\Invoice;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;

class Finder
{
    /**
     * @param Admin $admin
     *
     * @return bool
     */
    public static function isAllowedToFind(Admin $admin)
    {
        return !$admin->isPartner();
    }

    /**
     * @param Admin   $admin
     * @param Invoice $invoice
     *
     * @return bool
     */
    public static function isAllowedToAccess(Admin $admin, Invoice $invoice)
    {
        if (!self::isAllowedToFind($admin)) {
            return false;
        }

        if ($admin->hasAccessToAllEvent()) {
            return true;
        }

        return $admin->hasEvent($invoice->getSheet()->getEvent());
    }
}
