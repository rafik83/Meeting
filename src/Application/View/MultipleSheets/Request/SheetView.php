<?php

namespace Proximum\Vimeet\Application\View\MultipleSheets\Request;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetView
{
    /** @var int */
    public $sheetId;

    /** @var string */
    public $sheetTitle;

    /** @var Sheet */
    public $sheet;

    /** @var RequestView[] */
    public $requestViews;

    /** @var string */
    public $type;

    /** @var bool */
    public $isPhoneValidationRequired;

    /** @var null|string */
    public $validatePhoneLink;

    /**
     * @param int         $sheetId
     * @param string      $sheetTitle
     * @param Sheet       $sheet
     * @param string      $type
     * @param bool        $isPhoneValidationRequired
     * @param null|string $validatePhoneLink
     */
    public function __construct(
        $sheetId,
        $sheetTitle,
        Sheet $sheet,
        string $type = '',
        bool $isPhoneValidationRequired = false,
        ?string $validatePhoneLink = null
    ) {
        $this->sheetId                   = $sheetId;
        $this->sheetTitle                = $sheetTitle;
        $this->sheet                     = $sheet;
        $this->requestViews              = [];
        $this->type                      = $type;
        $this->isPhoneValidationRequired = $isPhoneValidationRequired;
        $this->validatePhoneLink         = $validatePhoneLink;
    }

    /**
     * @param RequestView $requestView
     */
    public function addRequest(RequestView $requestView)
    {
        $this->requestViews[] = $requestView;
    }

    /**
     * @return int
     */
    public function numberOfRequest()
    {
        return count($this->requestViews);
    }
}
