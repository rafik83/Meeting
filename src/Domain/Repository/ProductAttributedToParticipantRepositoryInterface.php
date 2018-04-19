<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;

interface ProductAttributedToParticipantRepositoryInterface
{
    public function add(ProductAttributedToParticipant $productAttributedToParticipant): void;
}
