<?php

namespace Proximum\Vimeet\Application\Adapter;

interface AuthorizationCheckerAdapterInterface
{
    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param mixed $attributes
     * @param mixed $object
     *
     * @return bool
     */
    public function isGranted($attributes, $object = null);
}
