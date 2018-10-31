<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Type;

use Proximum\Vimeet\Domain\Model\Type;

interface ContentRepositoryInterface
{
    public function add(Type\Content $content): void;
    public function update(Type\Content $content): void;

    public function findByTypeAndAssociatedParticipationType(string $type, Type $associatedParticipationType): ?Type\Content;

    public function remove(Type\Content $content): void;
}
