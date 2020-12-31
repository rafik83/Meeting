<?php

namespace Proximum\Vimeet\Application\Command\Register;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;

class ParticipantStep
{
    /**
     * @var int
     */
    public $step;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var array
     */
    public $data;

    /**
     * @param TemplateData $templateData
     * @param Participant  $participant
     * @param int          $step
     * @param string       $locale
     * @param array        $data
     */
    public function __construct(TemplateData $templateData, Participant $participant, $step, $locale, array $data)
    {
        $this->templateData = $templateData;
        $this->participant  = $participant;
        $this->sheet        = $participant->getSheet();
        $this->step         = $step;
        $this->locale       = $locale;
        $this->data         = $data;
    }
}
