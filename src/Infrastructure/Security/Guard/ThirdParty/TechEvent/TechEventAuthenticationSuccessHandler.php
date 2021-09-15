<?php

namespace Proximum\Vimeet\Infrastructure\Security\Guard\ThirdParty\TechEvent;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

class TechEventAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
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
