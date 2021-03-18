<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Package;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Package\PromotionCode\Add;
use Proximum\Vimeet\Application\Query\Package\Summary\SummaryViewQuery;
use Proximum\Vimeet\Domain\Cart\CartCleaner;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\FunnelFactory;
use Proximum\Vimeet\Domain\Package\Summary\TermsOfSale;
use Proximum\Vimeet\Domain\Promotion\Exception\PromotionCodeException;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\Summary\PromotionCodeType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\Summary\TermsOfSaleType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SummaryAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CartCleaner */
    private $cartCleaner;

    /** @var FunnelFactory */
    private $funnelFactory;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var BillingInfoRepositoryInterface */
    private $billingInfoRepository;

    /** @var EngineInterface */
    private $engine;

    /**
     * @param AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
     * @param CartCleaner                          $cartCleaner
     * @param FunnelFactory                        $funnelFactory
     * @param RouterInterface                      $router
     * @param BillingInfoRepositoryInterface       $billingInfoRepository
     * @param FlashBagInterface                    $flashBag
     * @param FormFactoryInterface                 $formFactory
     * @param CommandBusInterface                  $commandBus
     * @param QueryBusInterface                    $queryBus
     * @param EngineInterface                      $engine
     */
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CartCleaner $cartCleaner,
        FunnelFactory $funnelFactory,
        RouterInterface $router,
        BillingInfoRepositoryInterface $billingInfoRepository,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->cartCleaner = $cartCleaner;
        $this->funnelFactory = $funnelFactory;
        $this->router = $router;
        $this->billingInfoRepository = $billingInfoRepository;
        $this->flashBag = $flashBag;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->engine = $engine;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|Response
     */
    public function __invoke(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedException();
        }

        if (!$sheet->getPackage()->isPassable()) {
            throw new NotFoundHttpException(\sprintf('Package for sheet %s is not passable', $sheet->getId()));
        }

        $this->cartCleaner->handle($sheet);
        $funnel = $this->funnelFactory->create($sheet, $request->getLocale());

        if (!$funnel->isCompleted()) {
            return new RedirectResponse($this->router->generate('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => (null !== $funnel->getCartStep()) ? $funnel->getCartStep()->getCurrentStep() : 1,
            ]));
        }

        $billingInfo = $this->billingInfoRepository->getBySheet($sheet);

        // Redirect to the billing info action if the billing info are not completed
        if (null === $billingInfo || !$billingInfo->isCompleted()) {
            $this->flashBag->add('package_complete_billing_info', $sheet->getId());
            $this->flashBag->add('package_funnel_billing_info', true);

            return new RedirectResponse($this->router->generate('event_billing_info', [
                'sheet' => $sheet->getId(),
            ]));
        }

        $termsOfSale = new TermsOfSale();
        $formTermsOfSale = $this->formFactory->create(TermsOfSaleType::class, $termsOfSale);

        $command = new Add($sheet);
        $formPromotionCode = $this->formFactory->create(PromotionCodeType::class, $command);

        if ($formTermsOfSale->handleRequest($request)->isSubmitted() && $formTermsOfSale->isValid()) {
            $this->flashBag->add('package_completed_payment', $sheet->getId());

            return new RedirectResponse($this->router->generate('event_package_payment', [
                'sheet' => $sheet->getId(),
            ]));
        }

        if ($formPromotionCode->handleRequest($request)->isSubmitted() && $formPromotionCode->isValid()) {
            try {
                $this->commandBus->handle($command);
            } catch (PromotionCodeException $exception) {
                $this->flashBag->add('package_promotion_code_error', $exception->getFlash());
            }

            return new RedirectResponse($this->router->generate('event_package_summary', [
                'sheet' => $sheet->getId(),
                '_fragment' => 'summary-promo-code-row',
            ]));
        }

        $view = $this->queryBus->handle(
            new SummaryViewQuery(
                $sheet,
                $funnel,
                $funnel->getCart(),
                $request->getLocale()
            )
        );

        return $this->engine->renderResponse('EventBundle:Package:summary.html.twig', [
            'event'             => $eventDomain->getEvent(),
            'formTermsOfSale'   => $formTermsOfSale->createView(),
            'formPromotionCode' => $formPromotionCode->createView(),
            'sheet'             => $sheet,
            'view'              => $view,
        ]);
    }
}
