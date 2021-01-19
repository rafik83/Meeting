<?php

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetListViewQuery implements Query
{
    /** @var Sheet[] indexed by sheet id */
    public $sheets;

    /** @var string */
    public $locale;

    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /** @var FilterRequestView */
    public $filterRequestView;

    /** @var User */
    public $user;

    /**
     * @param User              $user
     * @param Sheet[]           $sheets indexed by sheet id
     * @param string            $locale
     * @param int               $page
     * @param int               $limit
     * @param FilterRequestView $filterRequestView
     */
    public function __construct(User $user, array $sheets, $locale, $page, $limit, FilterRequestView $filterRequestView)
    {
        foreach ($sheets as $sheetId => $sheet) {
            if ($sheetId !== $sheet->getId()) {
                throw new \InvalidArgumentException('Sheets array must be indexed by id');
            }
        }

        $this->sheets = $sheets;
        $this->locale = $locale;
        $this->page = $page;
        $this->limit = $limit;
        $this->filterRequestView = $filterRequestView;
        $this->user = $user;
    }
}
