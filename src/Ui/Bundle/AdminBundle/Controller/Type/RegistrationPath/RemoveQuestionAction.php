<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\RegistrationPath;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\RemoveQuestion;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveQuestionAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event, Question $question): RedirectResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $question->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $removeQuestion = new RemoveQuestion($question);
        $this->commandBus->handle($removeQuestion);

        $this->flashBag->add('success', 'flash.registrationPath.removeQuestion.success');

        return new RedirectResponse(
            $this->router->generate('admin_type_registration_path_show', ['event' => $event->getId()])
        );
    }
}
