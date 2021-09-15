<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;

class UpdateCompany extends AbstractUpdate
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param TemplateData     $templateData
     * @param Sheet            $sheet
     * @param Participant|null $participant
     * @param string           $locale
     * @param array            $data
     * @param User             $user
     */
    public function __construct(
        TemplateData $templateData,
        Sheet $sheet,
        Participant $participant = null,
        $locale,
        $data,
        User $user
    ) {
        parent::__construct($templateData, $participant, $locale, $data, $user);
        $this->sheet = $sheet;
    }
}
