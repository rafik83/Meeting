<?php

namespace Proximum\Vimeet\Domain\Model;

/**
 * Critère de validation automatique de la fiche de participation
 */
class ValidationCriteria
{
    /**
     * @var bool
     */
    private $sheetAccepted = false;

    /**
     * Validation Criteria Constructor
     *
     * @param bool $sheetAccepted
     */
    public function __construct($sheetAccepted = false)
    {
        $this->sheetAccepted = $sheetAccepted;
    }

    /**
     * @return bool
     */
    public function isSheetAccepted()
    {
        return $this->sheetAccepted;
    }

    /**
     * @param bool $sheetAccepted
     *
     * @return $this
     */
    public function setSheetAccepted($sheetAccepted)
    {
        $this->sheetAccepted = $sheetAccepted;

        return $this;
    }
}
