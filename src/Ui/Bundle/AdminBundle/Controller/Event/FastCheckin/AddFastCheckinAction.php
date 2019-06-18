<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\FastCheckin;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Event\Participant\AddFastCheckin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Participant\AddFastCheckinType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class AddFastCheckinAction
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FormFactory */
    private $formFactory;
    /**
     * @var EngineInterface
     */
    private $engine;
    /**
     * @var FlashBagInterface
     */
    private $flashBag;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        CommandBusInterface $commandBus,
        FormFactory $formFactory,
        EngineInterface $engine,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain, Event $event, string $email)
    {
        $addFastCheckin = new AddFastCheckin($event, $email);

        $form = $this->formFactory->create(
            AddFastCheckinType::class,
            $addFastCheckin,
            [
                'user' => $adminDomain->getAdmin(),
                'locale' => $request->getLocale(),
                'event' => $event,
            ]
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
//            try {
                $participant = $this->commandBus->handle($addFastCheckin);

                $this->flashBag->add('success', 'woot');

                return new RedirectResponse(
                    $this->router->generate('admin_event_qr_code_reader', ['event' => $event->getId()])
                );
//            } catch (\Exception $exception) {
//                $this->flashBag->add('error', 'oh no');
//            }
        }

        return $this->engine->renderResponse(
            '@Admin/Event/fastCheckinForm.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
