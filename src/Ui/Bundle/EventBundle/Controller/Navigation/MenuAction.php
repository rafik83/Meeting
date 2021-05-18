<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Navigation;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Query\Catalog\External\CatalogVisibilityRegistrationUrlQuery;
use Proximum\Vimeet\Application\Query\Navigation\HeaderViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\MenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\SubmenuViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\IsValidatedRequiredPackageMissing;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;
use Proximum\Vimeet\Domain\Transaction\IsValidatedTransactionMissing;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class MenuAction
{
    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var RequestStack */
    private $requestStack;
    /** @var IsValidatedRequiredPackageMissing */
    private $isValidatedRequiredPackageMissing;

    /** @var IsValidatedTransactionMissing */
    private $isValidatedTransactionMissing;

    /** @var StaticFormulationRepositoryInterface */
    private $staticFormulationRepository;

    public function __construct(
        RequestStack $requestStack,
        Environment $twig,
        QueryBusInterface $queryBus,
        IsValidatedRequiredPackageMissing $isValidatedRequiredPackageMissing,
        IsValidatedTransactionMissing $isValidatedTransactionMissing,
        StaticFormulationRepositoryInterface $staticFormulationRepository
    ) {
        $this->twig = $twig;
        $this->queryBus = $queryBus;
        $this->requestStack = $requestStack;
        $this->isValidatedRequiredPackageMissing = $isValidatedRequiredPackageMissing;
        $this->isValidatedTransactionMissing = $isValidatedTransactionMissing;
        $this->staticFormulationRepository = $staticFormulationRepository;
    }

    /**
     * @param Request         $request
     * @param EventDomain     $eventDomain
     * @param UserDomain|null $userDomain
     * @param Sheet|null      $sheet
     * @param bool            $registration
     *
     * @return Response
     */
    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain = null,
        Sheet $sheet = null,
        $registration = false
    ): Response {
        $event = $eventDomain->getEvent();
        $locale = $request->getLocale();
        $user = $userDomain instanceof UserDomain ? $userDomain->getUser() : null;

        $masterRequest = $this->requestStack->getMasterRequest();

        if (null === $masterRequest) {
            throw new AccessDeniedException('This controller must be used as embedded');
        }

        $route = $masterRequest->get('_route', Route::EVENT);
        $routeParameters = $masterRequest->get('_route_params');

        $menuHeaderView = $this->queryBus->handle(
            new HeaderViewQuery(
                $eventDomain->getEvent(),
                $request->getLocale(),
                $route,
                $routeParameters ?? [],
                $registration,
                $sheet,
                $user
            )
        );

        $menuView    = null;
        $submenuView = null;

        $canDisplayMenus = null !== $user && false === $registration &&
            (null === $sheet || !$this->isValidatedRequiredPackageMissing->isSatisfiedBy($sheet)) &&
            (null === $sheet || !$this->isValidatedTransactionMissing->isSatisfiedBy($sheet));

        if ($canDisplayMenus) {
            $staticFormulationsIndexedByCategories = [];

            if (null !== $sheet) {
                $staticFormulations = $this
                    ->staticFormulationRepository
                    ->findByTypeAndLocale(
                        $sheet->getType(),
                        $locale
                    )
                ;
                $staticFormulationsIndexedByCategories = [];

                foreach ($staticFormulations as $staticFormulation) {
                    if (!isset(Constant::STATIC_FORMULATION_LIST[$staticFormulation->getKey()])) {
                        continue;
                    }
                    $key = Constant::STATIC_FORMULATION_LIST[$staticFormulation->getKey()]['categoryKey'];
                    $staticFormulationsIndexedByCategories[$key] = $staticFormulation;
                }
            }


            $menuView = $this->queryBus->handle(
                new MenuViewQuery(
                    $event,
                    $locale,
                    $sheet,
                    $user,
                    $staticFormulationsIndexedByCategories
                )
            );

            $submenuView = $this->queryBus->handle(
                new SubmenuViewQuery(
                    $event,
                    $locale,
                    $route,
                    $sheet,
                    $user,
                    $staticFormulationsIndexedByCategories
                )
            );
        }

        if (Route::EXTERNAL_CATALOG === $route) {
            $registrationUrl = $this->queryBus->handle(
                new CatalogVisibilityRegistrationUrlQuery($event)
            );
        }

        $isShowingRegisterButton = Route::EVENT !== $route && null === $user;

        return new Response(
            $this->twig->render('EventBundle::Navigation/header.html.twig', [
                'menuHeaderView' => $menuHeaderView,
                'menuView' => $menuView,
                'submenuView' => $submenuView,
                'isShowingRegisterButton' => $isShowingRegisterButton,
                'isHeaderDisplayedOnMobile' => Route::isHeaderDisplayedOnMobile($route),
                'registrationUrl' => $registrationUrl ?? null,
            ])
        );
    }
}
