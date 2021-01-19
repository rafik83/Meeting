<?php

namespace Proximum\Vimeet\Domain\Constraint;

final class HexColorValidation
{
    public const HEX_REGEX = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
}
