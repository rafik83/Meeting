<?php

namespace Proximum\Vimeet\Application\View\Happening;

use JsonSerializable;
use Proximum\Vimeet\Application\View\Speaker\AbstractSpeakerView;

class ApiSpeakerView extends AbstractSpeakerView implements JsonSerializable
{
    public function jsonSerialize() {
        return [
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'position' => $this->getPosition(),
            'picture' => $this->getPicture(),
            'companyPicture' => $this->getCompanyPicture(),
        ];
    }
}
