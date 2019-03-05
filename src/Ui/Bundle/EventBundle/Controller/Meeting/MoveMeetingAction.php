<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Meeting;

use Proximum\Vimeet\Domain\Meeting\CanMoveMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class MoveMeetingAction
{
    /** @var CanMoveMeeting */
    private $canMoveMeeting;

    /** @var FormFactoryInterface $formFactory */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    public function __construct(
        CanMoveMeeting $canMoveMeeting,
        FormFactoryInterface $formFactory,
        EngineInterface $engine
    ) {
        $this->canMoveMeeting = $canMoveMeeting;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
    }

    public function __invoke(Request $request, Sheet $sheet, Meeting $meeting): Response
    {
        if (false === $this->canMoveMeeting->isSatisfiedBy($sheet)) {
            throw new AccessDeniedException();
        }

        $form = $this->formFactory->create();

        return new Response(
            $this->engine->render('@Event/Meeting/move-meeting-form.html.twig', [
                'form' => $form->createView(),
            ])
        );
    }
}
