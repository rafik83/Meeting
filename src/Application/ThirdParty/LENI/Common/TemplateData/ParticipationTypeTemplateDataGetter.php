<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\TemplateData;

use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class ParticipationTypeTemplateDataGetter
{
    /** @var TemplateData[] indexed by Type id */
    private $registrationTemplateByType = [];

    /** @var TemplateData[] indexed by Type id */
    private $sheetTemplateByType = [];

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param Type $type
     *
     * @return TemplateData
     */
    public function getRegistrationTemplateDataByType(Type $type): TemplateData
    {
        if (isset($this->registrationTemplateByType[$type->getId()])) {
            $registrationTemplateData = $this->registrationTemplateByType[$type->getId()];
            $registrationTemplateData->clear();

            return $registrationTemplateData;
        }

        $registrationTemplateData = $this->templateDataFactory->createRegistrationFromType($type, null);
        $this->registrationTemplateByType[$type->getId()] = $registrationTemplateData;

        return $registrationTemplateData;
    }

    /**
     * @param Type $type
     *
     * @return TemplateData
     */
    public function getSheetTemplateDataByType(Type $type): TemplateData
    {
        if (isset($this->sheetTemplateByType[$type->getId()])) {
            $sheetTemplateData = $this->sheetTemplateByType[$type->getId()];
            $sheetTemplateData->clear();

            return $sheetTemplateData;
        }

        $sheetTemplateData = $this->templateDataFactory->createSheetTemplateFromType($type);
        $this->sheetTemplateByType[$type->getId()] = $sheetTemplateData;

        return $sheetTemplateData;
    }
}
