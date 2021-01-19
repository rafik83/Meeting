<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;

interface SubstituteInterface
{
    /**
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return string
     */
    public function getValue(Sheet $sheet, $locale);
}
