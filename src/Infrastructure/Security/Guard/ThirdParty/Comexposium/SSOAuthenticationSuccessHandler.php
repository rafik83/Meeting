<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\Comexposium;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

final class SSOAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @param HttpUtils $httpUtils
     */
    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
        $this->providerKey = 'main';

        parent::__construct($httpUtils, [
            'use_referer' => true,
        ]);
    }
}
