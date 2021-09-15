<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Category\Create;
use Proximum\Vimeet\Application\Command\Category\Update;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Category\CategoryCreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Category\CategoryUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractController
{
    private CategoryRepositoryInterface $categoryRepository;
    private CommandBusInterface $commandBus;

    public function __construct(CategoryRepositoryInterface $categoryRepository, CommandBusInterface $commandBus)
    {
        $this->categoryRepository = $categoryRepository;
        $this->commandBus = $commandBus;
    }

    public function listAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $categories = $this->categoryRepository
            ->paginate($request->query->get('page', 1), 20, $event->getId(), $event->getAvailableLocale($request->getLocale()));

        return $this->render('AdminBundle:Category:list.html.twig', [
            'event'      => $event,
            'categories' => $categories,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function createAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new Create($event);
        $form   = $this->createForm(CategoryCreateType::class, $create, [
            'method' => 'POST',
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->addFlash('success', 'flash.admin.category.create.success');

            return $this->redirectToRoute('admin_category_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Category:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request  $request
     * @param Event    $event
     * @param Category $category
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, Category $category)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($event !== $category->getEvent()) {
            throw $this->createNotFoundException('Category not found.');
        }

        $update = new Update($category);
        $form   = $this->createForm(CategoryUpdateType::class, $update, [
            'method' => 'POST',
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->addFlash('success', 'flash.admin.category.update.success');

            return $this->redirectToRoute('admin_category_list', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Category:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
