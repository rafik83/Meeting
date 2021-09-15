<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Chat;

use Proximum\Vimeet\Application\Adapter\AvatarInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AvatarAction
{
    /** @var AvatarInterface */
    private $avatarAdapter;

    public function __construct(AvatarInterface $avatarAdapter)
    {
        $this->avatarAdapter = $avatarAdapter;
    }

    public function __invoke(
        Request $request
    ): Response {
        $name = $request->query->get('name');
        if (empty($name)) {
            throw new BadRequestHttpException('Missing name parameter');
        }

        $response = new Response($this->avatarAdapter->generate($name));
        $response->headers->set('Content-type', 'image/png');
        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}
