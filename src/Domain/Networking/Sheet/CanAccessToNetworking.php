<?php


namespace Proximum\Vimeet\Domain\Networking\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class CanAccessToNetworking
{
    public function isSatisfied(Sheet $sheet): bool
    {
        return $sheet->isAccepted() || $sheet->isValidated();
    }

}
