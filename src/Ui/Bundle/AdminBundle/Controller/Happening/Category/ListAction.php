<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Happening\Category;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListAction
{
    const TEMPLATE = 'AdminBundle:Happening/Category:list.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var EngineInterface */
    private $engine;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param CategoryRepositoryInterface          $categoryRepository
     * @param EngineInterface                      $engine
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CategoryRepositoryInterface $categoryRepository,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->categoryRepository = $categoryRepository;
        $this->engine = $engine;
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException();
        }

        $categories = $this->categoryRepository->findByEvent(
            $event,
            $event->getAvailableLocale($request->getLocale())
        );

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event'      => $event,
            'categories' => $categories,
        ]);
    }
}
