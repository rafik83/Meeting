<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\PromotionCode\Create;
use Proximum\Vimeet\Application\Command\PromotionCode\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Exception\NonUniqueCodeException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PromotionCodeController extends Controller
{
    /**
     * @param Event $event
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $promotionCodes = $this->get('repository.promotion_code_repository')->findWithoutGroupByEvent($event);

        return $this->render('AdminBundle:PromotionCode:list.html.twig', [
            'event'           => $event,
            'promotion_codes' => $promotionCodes,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create       = new Create($event);
        $create->code = $this->get('promotion.generator.unique_code_generator')->generate($event);
        $form         = $this->createForm(CreateType::class, $create, [
            'submit' => true,
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($create);
                $this->addFlash('success', 'flash.promotion_code.create.success');

                return $this->redirectToRoute('admin_promotion_code_list', [
                    'event' => $event->getId(),
                ]);
            } catch (NonUniqueCodeException $exception) {
                $form->get('code')->addError($this->createNonUniqueCodeError($request->getLocale()));
            }
        }

        return $this->render('AdminBundle:PromotionCode:create.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }

    /**
     * @param Request       $request
     * @param Event         $event
     * @param PromotionCode $promotionCode
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, PromotionCode $promotionCode)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->notFoundIfWrongPromotionCodeEvent($event, $promotionCode);

        if ($promotionCode->hasPromotionCodeGroup()) {
            throw $this->createAccessDeniedException('Promotion code has a group.');
        }

        $update = new Update($promotionCode);
        $form   = $this->createForm(UpdateType::class, $update, [
            'submit' => true,
            'event'  => $event,
            'can_update_promotions' => !$this
                ->get('vimeet_infrastructure.repository.order_repository')
                ->hasOrderWithPromotionCode($promotionCode)
            ,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($update);
                $this->addFlash('success', 'flash.promotion_code.update.success');

                return $this->redirectToRoute('admin_promotion_code_list', [
                    'event' => $event->getId(),
                ]);
            } catch (NonUniqueCodeException $exception) {
                $form
                    ->get('code')
                    ->addError($this->createNonUniqueCodeError(
                        $event->getAvailableLocale($request->getLocale())
                    ))
                ;
            }
        }

        return $this->render('AdminBundle:PromotionCode:update.html.twig', [
            'promotion_code' => $promotionCode,
            'event'          => $event,
            'form'           => $form->createView(),
        ]);
    }

    /**
     * @param Event         $event
     * @param PromotionCode $promotionCode
     */
    private function notFoundIfWrongPromotionCodeEvent(Event $event, PromotionCode $promotionCode)
    {
        if ($event !== $promotionCode->getEvent()) {
            throw $this->createNotFoundException('Promotion code not found.');
        }
    }

    /**
     * @param string $locale
     *
     * @return FormError
     */
    private function createNonUniqueCodeError($locale)
    {
        return $this->get('error_factory')->create('validators.promotion_code.code.already_exist', $locale);
    }
}
