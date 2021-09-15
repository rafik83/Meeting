<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\PromotionCode\Create;
use Proximum\Vimeet\Application\Command\PromotionCode\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\Exception\NonUniqueCodeException;
use Proximum\Vimeet\Domain\Promotion\Generator\UniqueCodeGenerator;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\ErrorFactory;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PromotionCodeController extends AbstractController
{
    private ErrorFactory $errorFactory;
    private PromotionCodeRepositoryInterface $promotionCodeRepository;
    private UniqueCodeGenerator $uniqueCodeGenerator;
    private OrderRepositoryInterface $orderRepository;
    private CommandBusInterface $commandBus;

    public function __construct(
        ErrorFactory $errorFactory,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        UniqueCodeGenerator $uniqueCodeGenerator,
        OrderRepositoryInterface $orderRepository,
        CommandBusInterface $commandBus
    ) {
        $this->errorFactory = $errorFactory;
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->uniqueCodeGenerator = $uniqueCodeGenerator;
        $this->orderRepository = $orderRepository;
        $this->commandBus = $commandBus;
    }

    public function listAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $promotionCodes = $this->promotionCodeRepository->findWithoutGroupByEvent($event);

        return $this->render('AdminBundle:PromotionCode:list.html.twig', [
            'event' => $event,
            'promotion_codes' => $promotionCodes,
        ]);
    }

    public function createAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new Create($event);
        $create->code = $this->uniqueCodeGenerator->generate($event);
        $form = $this->createForm(CreateType::class, $create, [
            'submit' => true,
            'event' => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($create);
                $this->addFlash('success', 'flash.promotion_code.create.success');

                return $this->redirectToRoute('admin_promotion_code_list', [
                    'event' => $event->getId(),
                ]);
            } catch (NonUniqueCodeException $exception) {
                $form->get('code')->addError($this->createNonUniqueCodeError($request->getLocale()));
            }
        }

        return $this->render('AdminBundle:PromotionCode:create.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    public function updateAction(Request $request, Event $event, PromotionCode $promotionCode): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->notFoundIfWrongPromotionCodeEvent($event, $promotionCode);

        if ($promotionCode->hasPromotionCodeGroup()) {
            throw $this->createAccessDeniedException('Promotion code has a group.');
        }

        $update = new Update($promotionCode);
        $form = $this->createForm(UpdateType::class, $update, [
            'submit' => true,
            'event' => $event,
            'can_update_promotions' => !$this->orderRepository
                ->hasOrderWithPromotionCode($promotionCode)
            ,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($update);
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
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    private function notFoundIfWrongPromotionCodeEvent(Event $event, PromotionCode $promotionCode)
    {
        if ($event !== $promotionCode->getEvent()) {
            throw $this->createNotFoundException('Promotion code not found.');
        }
    }

    private function createNonUniqueCodeError(string $locale): FormError
    {
        return $this->errorFactory->create('validators.promotion_code.code.already_exist', $locale);
    }
}
