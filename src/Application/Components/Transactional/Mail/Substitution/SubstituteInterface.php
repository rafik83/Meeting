<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

use Proximum\Vimeet\Application\Components\Transactional\Mail\View\AbstractPrepareMail;

interface SubstituteInterface
{
    public function substitute(AbstractPrepareMail $prepareMail): string;
}
