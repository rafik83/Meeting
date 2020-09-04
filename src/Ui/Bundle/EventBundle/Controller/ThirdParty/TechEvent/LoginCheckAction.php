<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\ThirdParty\TechEvent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class LoginCheckAction
{
    public function __invoke(Request $request)
    {
        throw new AccessDeniedException('Should never be reached.');
    }
}
