<?php

namespace Proximum\Vimeet\Domain\Repository\Rooming;

use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;

interface AccommodationRepositoryInterface
{
    public function add(Accommodation $accommodation): void;
}
