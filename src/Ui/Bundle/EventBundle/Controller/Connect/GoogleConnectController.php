<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Connect;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GoogleConnectController extends Controller
{
    /** @var ClientRegistry */
    private $clientRegistry;

    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
    }

    public function connectAction()
    {
        return $this->clientRegistry
            ->getClient('google_main')
            ->redirect(
                [
                    'https://www.googleapis.com/auth/userinfo.email',
                ]
            );
    }
}
