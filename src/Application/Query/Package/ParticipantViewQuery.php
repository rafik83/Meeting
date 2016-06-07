<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;

class ParticipantViewQuery
{
    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Product
     */
    public $participantProduct;

    /**
     * @var bool
     */
    public $included;

    /**
     * @param Product     $participantProduct
     * @param Participant $participant
     * @param string      $locale
     * @param bool        $included
     */
    public function __construct(Product $participantProduct, Participant $participant, $locale, $included)
    {
        $this->participantProduct = $participantProduct;
        $this->participant        = $participant;
        $this->locale             = $locale;
        $this->included           = $included;
    }
}
