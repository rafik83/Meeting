<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Sheet;

interface SheetProtectedKeyPasswordGetterInterface
{
    public function getProtectedKeyPasswordBySheet(Sheet $sheet): string;
}
