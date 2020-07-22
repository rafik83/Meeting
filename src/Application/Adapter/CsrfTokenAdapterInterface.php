<?php

namespace Proximum\Vimeet\Application\Adapter;

interface CsrfTokenAdapterInterface
{
    public function isTokenValid(string $id, ?string $submittedValue): bool;
}
