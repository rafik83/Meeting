<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Sheet;

interface SheetProtectedKeyGetterInterface
{
    public function getProtectedKeyBySheet(Sheet $sheet): string;
}
