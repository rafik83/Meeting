<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
