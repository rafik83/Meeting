<?php

namespace Proximum\Vimeet\Application\Adapter;

interface ImpersonatingUserCheckerInterface
{
    /**
     * @return bool
     */
    public function isImpersonated();

    /**
     * Return true if current user have ROLE_PREVIOUS_ADMIN
     *
     * @return bool
     */
    public function isPreviousAdmin();

    /**
     * Return true if current user have ROLE_PREVIOUS_USER
     *
     * @return bool
     */
    public function isPreviousUser();
}
