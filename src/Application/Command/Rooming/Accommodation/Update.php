<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Accommodation;

use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;

class Update extends AbstractAccommodationCommand
{
    /** @var Accommodation */
    public $accommodation;

    public function __construct(Accommodation $accommodation)
    {
        $this->accommodation = $accommodation;
        $this->title = $accommodation->getTitle();

        foreach ($accommodation->getOvernightCapacities() as $overnightCapacity) {
            $this->overnightCapacities[] = new AccommodationOvernightCapacityView(
                $overnightCapacity->getDate(),
                $overnightCapacity->getCapacity()
            );
        }
    }
}
