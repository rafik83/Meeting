<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Security;

use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\AbstractRedirectToEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CatalogAccessEventListener extends AbstractRedirectToEventListener
{
    const ROUTES = [
        'event_catalog_redirect',
        'event_catalog_index',
        'event_catalog_search_localization',
        'event_catalog_search_keywords',
    ];

    const EXTERNAL_ROUTES = [
        'event_catalog_external_index',
    ];

    /**
     * @var CatalogAccessChecker
     */
    private $catalogAccessChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * CatalogAccessEventListener constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param CatalogAccessChecker     $catalogAccessChecker
     * @param EventRepositoryInterface $eventRepository
     * @param TokenStorageInterface    $tokenStorage
     * @param RouterInterface          $router
     * @param string                   $adminDomain
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        CatalogAccessChecker $catalogAccessChecker,
        EventRepositoryInterface $eventRepository,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        string $adminDomain
    ) {
        parent::__construct($router, $eventRepository, $adminDomain);

        $this->catalogAccessChecker = $catalogAccessChecker;
        $this->tokenStorage         = $tokenStorage;
        $this->sheetRepository      = $sheetRepository;
    }

    /**
     * @param GetResponseEvent $getResponseEvent
     */
    public function accessChecker(GetResponseEvent $getResponseEvent)
    {
        $this->handleRedirect($getResponseEvent);
    }

    /**
     * {@inheritdoc}
     */
    protected function isIgnoredRoute($route)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRedirect(GetResponseEvent $getResponseEvent, Request $request, Event $event, $locale, $route)
    {
        // check if internal catalog opened
        if (in_array($route, self::ROUTES)) {
            if (!$this->catalogAccessChecker->allowedToAccess($event)) {
                throw new NotFoundHttpException();
            }
        }

        // redirect from external catalog to internal catalog when Sheet is in catalog and catalog is opened
        if (in_array($route, self::EXTERNAL_ROUTES)) {
            $sheet = $this->getUserSheetAllowedToAccessCatalog($event);

            if (null !== $sheet) {
                $getResponseEvent->setResponse(
                    $this->createRedirectResponse(
                        $request,
                        $event,
                        'event_catalog_index',
                        $locale,
                        ['sheet' => $sheet->getId()]
                    )
                );
            }

            if (!$this->catalogAccessChecker->allowedToAccessExternal($event)) {
                throw new NotFoundHttpException();
            }
        }
    }

    /**
     * @param Event $event
     *
     * @return null|Sheet
     */
    protected function getUserSheetAllowedToAccessCatalog(Event $event): ?Sheet
    {
        if (!$this->catalogAccessChecker->allowedToAccess($event)) {
            return null;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof User) {
            return null;
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        foreach ($sheets as $sheet) {
            if ($sheet->isInCatalog()) {
                return $sheet;
            }
        }

        return null;
    }
}
