<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassTimeSlot;

class Update extends Base
{
    /**
     * @var Mass
     */
    public $mass;

    /**
     * @param Mass $mass
     */
    public function __construct(Mass $mass)
    {
        $this->mass      = $mass;
        $this->begin     = $mass->getBegin();
        $this->end       = $mass->getEnd();
        $this->blocking  = $mass->isBlocking();
        $this->name      = $mass->getName();
        $this->category  = $mass->getCategory();
        $this->dispatch  = $mass->isDispatch();

        $this->types = $mass->getTypes();

        $this->timeSlots = array_map(function (MassTimeSlot $timeSlot) {
            return ['from' => $timeSlot->getFrom(), 'to' => $timeSlot->getTo()];
        }, $mass->getTimeSlots());

        foreach ($mass->getTranslations() as $locale => $translation) {
            $this->translations[$locale] = [
                'title'       => $translation->getTitle(),
                'description' => $translation->getDescription(),
            ];
        }
    }
}
