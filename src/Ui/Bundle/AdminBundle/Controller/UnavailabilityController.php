<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Unavailability\Category\Create as CreateCategory;
use Proximum\Vimeet\Application\Command\Unavailability\Category\Update as UpdateCategory;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Create as CreateMass;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Delete;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Dispatcher;
use Proximum\Vimeet\Application\Command\Unavailability\Mass\Update as UpdateMass;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Unavailability\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category\CreateType as CreateCategoryType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Category\UpdateType as UpdateCategoryType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Mass\CreateType as CreateMassType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability\Mass\UpdateType as UpdateMassType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UnavailabilityController extends AbstractController
{
    private MassRepositoryInterface $massUnavailabilityRepository;
    private CategoryRepositoryInterface $unavailabilityCategoryRepository;
    private CommandBusInterface $commandBus;

    public function __construct(
        MassRepositoryInterface $massUnavailabilityRepository,
        CategoryRepositoryInterface $unavailabilityCategoryRepository,
        CommandBusInterface $commandBus
    ) {
        $this->massUnavailabilityRepository = $massUnavailabilityRepository;
        $this->unavailabilityCategoryRepository = $unavailabilityCategoryRepository;
        $this->commandBus = $commandBus;
    }

    public function massListAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $massList = $this->massUnavailabilityRepository->findByEvent(
            $event,
            $event->getAvailableLocale($request->getLocale())
        );

        return $this->render('AdminBundle:Unavailability/Mass:list.html.twig', [
            'event'    => $event,
            'massList' => $massList,
        ]);
    }

    public function dispatchAction(Event $event): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        try {
            $this->commandBus->handle(new Dispatcher($event));
            $this->addFlash('success', 'flash.admin.unavailability.mass.dispatch.success');
        } catch (UnableToDispatchException $exception) {
            $this->addFlash('error', $exception->indication);
        }

        return $this->redirectToRoute('admin_unavailability_mass_list', ['event' => $event->getId()]);
    }

    public function createMassAction(Request $request, AdminDomain $adminDomain, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $days = $event->getDays();
        $day  = reset($days);

        if (false === $day) {
            $day = null;
        }

        $create = new CreateMass($event, $day);
        $form = $this->createForm(CreateMassType::class, $create, [
            'event' => $event,
            'user' => $adminDomain->getAdmin(),
            'locale' => $request->getLocale(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->addFlash('success', 'flash.admin.unavailability.mass.create.success');

            return $this->redirectToRoute('admin_unavailability_mass_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Unavailability/Mass:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    public function updateMassAction(Request $request, AdminDomain $adminDomain, Event $event, Mass $mass): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($event !== $mass->getEvent()) {
            throw $this->createNotFoundException('This mass is not on this event');
        }

        $update = new UpdateMass($mass);
        $form = $this->createForm(UpdateMassType::class, $update, [
            'event' => $event,
            'user' => $adminDomain->getAdmin(),
            'locale' => $request->getLocale(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->addFlash('success', 'flash.admin.unavailability.mass.update.success');

            return $this->redirectToRoute('admin_unavailability_mass_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Unavailability/Mass:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    public function deleteMassAction(Event $event, Mass $mass): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $this->commandBus->handle(new Delete($mass));
        $this->addFlash('success', 'flash.admin.unavailability.mass.delete.success');

        return $this->redirectToRoute('admin_unavailability_mass_list', ['event' => $event->getId()]);
    }

    public function categoryListAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $categories = $this->unavailabilityCategoryRepository->findByEvent($event);

        return $this->render('AdminBundle:Unavailability/Category:list.html.twig', [
            'event'      => $event,
            'categories' => $categories,
        ]);
    }

    public function createCategoryAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreateCategory($event);
        $form = $this->createForm(CreateCategoryType::class, $create, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->addFlash('success', 'flash.admin.unavailability.category.create.success');

            return $this->redirectToRoute('admin_unavailability_category_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Unavailability/Category:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    public function updateCategoryAction(Request $request, Event $event, Category $category): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($event !== $category->getEvent()) {
            throw $this->createNotFoundException('This category is not on this event');
        }

        $update = new UpdateCategory($category);
        $form = $this->createForm(UpdateCategoryType::class, $update, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->addFlash('success', 'flash.admin.unavailability.category.update.success');

            return $this->redirectToRoute('admin_unavailability_category_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Unavailability/Category:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
