<?php

namespace Proximum\Vimeet\Application\Query\Dashboard\View;

class DashboardUserAndTypeScanView
{
    /** @var int */
    private $userId;

    /** @var int */
    private $sheetTypeId;

    public function __construct(int $userId, int $sheetTypeId)
    {
        $this->userId = $userId;
        $this->sheetTypeId = $sheetTypeId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getSheetTypeId(): int
    {
        return $this->sheetTypeId;
    }
}
