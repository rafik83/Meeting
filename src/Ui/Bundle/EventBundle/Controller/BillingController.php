<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Billing\UpdateInfo;
use Proximum\Vimeet\Domain\Billing\Prefiller;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\FunnelFactory;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Billing\UpdateInfoType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class BillingController extends AbstractController
{
    private BillingInfoRepositoryInterface $billingInfoRepository;
    private Prefiller $billingPrefiller;
    private FunnelFactory $funnelFactory;
    private FlashBagInterface $flashBag;
    private CommandBusInterface $commandBus;

    public function __construct(
        BillingInfoRepositoryInterface $billingInfoRepository,
        Prefiller $billingPrefiller,
        FunnelFactory $funnelFactory,
        FlashBagInterface $flashBag,
        CommandBusInterface $commandBus
    ) {
        $this->billingInfoRepository = $billingInfoRepository;
        $this->billingPrefiller = $billingPrefiller;
        $this->funnelFactory = $funnelFactory;
        $this->flashBag = $flashBag;
        $this->commandBus = $commandBus;
    }

    public function infoAction(Request $request, EventDomain $eventDomain, Sheet $sheet): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $info    = $this->billingInfoRepository->getBySheet($sheet) ?: new BillingInfo($sheet);
        $country = $sheet->getEvent()->getCountry();

        if (null === $info->getId()) {
            $this->billingPrefiller->prefill($info);
        }

        $command = new UpdateInfo($info);
        $form    = $this->createForm(UpdateInfoType::class, $command, ['submit' => true, 'country' => $country]);

        $packageCompleteBilling = $this->getFlash('package_complete_billing_info');
        $funnel = null;

        if (null !== $this->getFlash('package_funnel_billing_info')) {
            $funnel = $this->funnelFactory->create($sheet, $request->getLocale());
            $this->addFlash('package_funnel_billing_info', true);
        }

        if (null !== $packageCompleteBilling) {
            $this->addFlash('package_complete_billing_info', $packageCompleteBilling);
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);

            // Redirect to package summary if coming from Package Summary
            if (null !== $packageCompleteBilling) {
                return $this->redirectToRoute('event_package_summary', [
                    'sheet' => $sheet->getId(),
                ]);
            } else {
                $this->addFlash('success', 'flash.billing.update_info.success');

                return $this->redirectToRoute('event_billing_info', [
                    'sheet' => $sheet->getId(),
                ]);
            }
        }

        return $this->render('EventBundle:Billing:info.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'form'  => $form->createView(),
            'view'  => ['funnel' => $funnel],
        ]);
    }

    /**
     * Route used in navigation menu to redirect into billing info form
     * and clean flash to prevent the funnel display
     */
    public function infoClearFlashAction(Sheet $sheet): RedirectResponse
    {
        $this->flashBag->set('package_funnel_billing_info', null);

        return $this->redirectToRoute('event_billing_info', ['sheet' => $sheet->getId()]);
    }

    /**
     * @return mixed|null
     */
    private function getFlash(string $flash)
    {
        $sheet = $this->flashBag->get($flash);

        return array_shift($sheet);
    }
}
