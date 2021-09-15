<?php

namespace Proximum\Vimeet\Application\Criteria;

interface Criteria
{
    public function meetCriteria(array $values): array;
}
