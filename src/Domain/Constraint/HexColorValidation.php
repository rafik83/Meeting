<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Constraint;

final class HexColorValidation
{
    public const HEX_REGEX = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
}
