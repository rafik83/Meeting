<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Package\AddProducts;
use Proximum\Vimeet\Application\Command\Package\UpdateStep;
use Proximum\Vimeet\Application\Exception\Package\BoughtParticipantAlreadyAddedException;
use Proximum\Vimeet\Application\Exception\Package\ForgotToAddQuantityException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package\AddProductsType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package\UpdateStepType;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PackageController extends BaseController
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param int       $step
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @return RedirectResponse|Response
     */
    public function updateStepAction(Request $request, EventView $eventView, Sheet $sheet, $step)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());
        $this->denyAccessPackageStepNotExists($sheet, $step);
        $this->denyAccessAfterFirstOrderGenerated($sheet);

        $cart = $this->get('vimeet_infrastructure.application.components.cart.cart_manager')
            ->findOrCreateCart($sheet);

        $template = $this->get('vimeet_infrastructure.application.components.product.product_builder')
            ->createFromCart($sheet->getType(), $cart);

        $stepObject = $template->getStep($step);

        if ($stepObject === null) {
            throw $this->createNotFoundException();
        }

        $updateStep = new UpdateStep($cart, $sheet, $step);
        $form       = $this->createForm(UpdateStepType::class, $updateStep, [
            'template' => $sheet->getTypePackageTemplate()[$step]['template'],
            'locale'   => $request->getLocale(),
            'sheet'    => $sheet,
            'cart'     => $cart,
            'step'     => $stepObject,
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.package.update_step_handler')
                    ->handle($updateStep);

                return $this->redirect($this->urlAfterUpdateStep($request, $sheet, $step));
            } catch (BoughtParticipantAlreadyAddedException $exception) {
                $packageTemplate = $sheet->getTypePackageTemplate();
                $this->addErrorOnForm(
                    $exception,
                    $form,
                    $packageTemplate[$step],
                    $updateStep->packageData,
                    $form->get('packageData')
                );
            } catch (ForgotToAddQuantityException $exception) {
                $packageTemplate = $sheet->getTypePackageTemplate();
                $this->addErrorOnForm(
                    $exception,
                    $form,
                    $packageTemplate[$step],
                    $updateStep->packageData,
                    $form->get('packageData')
                );
            }
        }

        return $this->render('VimeetAppBundle:Event/Package:updateStep.html.twig', [
            'eventView'           => $eventView,
            'sheet'               => $sheet,
            'stepPackageTemplate' => $sheet->getTypePackageTemplate()[$step],
            'form'                => $form->createView(),
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function cartAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $cart = $this->get('vimeet_infrastructure.repository.cart_repository')->findBySheet($sheet);

        if ($cart === null) {
            throw $this->createNotFoundException('Cart not available');
        }
        $template = $this->get('vimeet_infrastructure.application.components.product.product_builder')
            ->createFromCart($sheet->getType(), $cart);
        $cart     = $this->get('vimeet_infrastructure.application.components.cart.cart_builder')
            ->generate(
                $template,
                $cart->getData(),
                $request->getLocale()
            )
        ;

        return $this->render('VimeetAppBundle:Event/Package:cart.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'cart'      => $cart,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function addProductsAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $cart = $this->get('vimeet_infrastructure.application.components.cart.cart_manager')->findOrCreateCart($sheet);
        $template = $this->get('vimeet_infrastructure.application.components.product.product_builder')
            ->createFromCart($sheet->getType(), $cart);

        $addProducts = new AddProducts($cart, $sheet);
        $form        = $this->createForm(AddProductsType::class, $addProducts, [
            'productTemplate' => $template,
            'packageTemplate' => $sheet->getTypePackageTemplate(),
            'cart'            => $cart,
            'sheet'           => $sheet,
            'locale'          => $request->getLocale(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.package.add_products_handler')
                    ->handle($addProducts);

                $this->addFlash('success', 'flash.package.add_products.success');

                return $this->redirectToRoute('event_sheet_package_cart', [
                    'subdomain' => $request->attributes->get('subdomain'),
                    'id'        => $sheet->getId(),
                ]);
            } catch (ForgotToAddQuantityException $exception) {
                $this->addErrorOnFormPackage(
                    $exception,
                    $addProducts->packageData,
                    $form
                );
            }
        }

        return $this->render('VimeetAppBundle:Event/Package:products.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Sheet $sheet
     * @param int   $step
     *
     * @throws NotFoundHttpException
     */
    private function denyAccessPackageStepNotExists(Sheet $sheet, $step)
    {
        $packageTemplate = $sheet->getTypePackageTemplate();

        if (!isset($packageTemplate[$step])) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @param Sheet $sheet
     *
     * @throws NotFoundHttpException
     */
    private function denyAccessAfterFirstOrderGenerated(Sheet $sheet)
    {
        if (!$sheet->getOrders()->isEmpty()) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @param Request $request
     * @param Sheet   $sheet
     * @param int     $step
     *
     * @return string
     */
    private function urlAfterUpdateStep(Request $request, Sheet $sheet, $step)
    {
        $redirectTo      = $request->get('redirect_to');
        $packageTemplate = $sheet->getTypePackageTemplate();

        if (null !== $redirectTo) {
            $this->addFlash('success', 'flash.package.update_step.success');

            return $redirectTo;
        } elseif ($nextStep = self::nextStep($step, array_keys($packageTemplate))) {
            $this->addFlash('success', 'flash.package.update_step.success');

            return $this->generateUrl('event_sheet_package_update_step', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
                'step'      => $nextStep,
            ]);
        }

        $this->addFlash('success', 'flash.package.final_step.success');

        return $this->generateUrl('event_sheet_package_cart', [
            'subdomain' => $request->attributes->get('subdomain'),
            'id'        => $sheet->getId(),
        ]);
    }

    /**
     * @param string $current
     * @param array  $array
     *
     * @return string|null
     */
    private static function nextStep($current, array $array)
    {
        foreach ($array as $key => $value) {
            if ($value === $current) {
                return isset($array[$key + 1]) ? $array[$key + 1] : null;
            }
        }

        return;
    }
}
