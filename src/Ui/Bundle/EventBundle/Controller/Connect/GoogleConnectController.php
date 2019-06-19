<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Connect;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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

    public function connectCheckAction(Request $request)
    {
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\GoogleClient $client */
        $client = $this->clientRegistry->getClient('google_main');

        try {
            // the exact class depends on which provider you're using
            /** @var \League\OAuth2\Client\Provider\GoogleUser $user */
            $user = $client->fetchUser();

            // do something with all this new power!
            // e.g. $name = $user->getFirstName();
            dump($user);
            die;
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            dump($e->getMessage());
            die;
        }
    }
}
