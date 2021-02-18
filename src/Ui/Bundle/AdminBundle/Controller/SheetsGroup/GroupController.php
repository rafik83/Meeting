<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\SheetsGroup;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Sheet\Group\Create;
use Proximum\Vimeet\Application\Command\Sheet\Group\SearchUser;
use Proximum\Vimeet\Application\Command\Sheet\Group\Update;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyGroupManagerOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserAlreadyParticipantOrOwnerOnGroupOnSameEventException;
use Proximum\Vimeet\Application\Exception\Group\UserNotAllowedToManageGroupException;
use Proximum\Vimeet\Application\Exception\Group\UserNotFoundForGivenEmailException;
use Proximum\Vimeet\Application\Query\Group\Sheet\SheetViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Service\SheetsGroup\UserToGroupManagerChecker;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Group\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Group\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GroupController extends AbstractController
{
    private UserToGroupManagerChecker $userToGroupManagerChecker;
    private TranslatorInterface $translator;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        UserToGroupManagerChecker $userToGroupManagerChecker,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->userToGroupManagerChecker = $userToGroupManagerChecker;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    /**
     * Search user by email to pre-populate the real create form
     */
    public function preCreateAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_OPERATE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $searchUser = new SearchUser($event);
        $form       = $this->createForm(SearchType::class, $searchUser, ['event' => $event]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $userView = $this->commandBus->handle($searchUser);

                return $this->redirectToRoute('admin_sheets_group_create', [
                    'event' => $event->getId(),
                    'user'  => $userView->id,
                ]);
            } catch (UserNotAllowedToManageGroupException $exception) {
                $this->notifyFormError($form, 'email', 'validators.group.user_not_allowed_to_manage', ['%email%' => $exception->email]);
            } catch (UserNotFoundForGivenEmailException $exception) {
                $this->notifyFormError($form, 'email', 'validators.group.email_not_found', ['%email%' => $exception->email]);
            }
        }

        return $this->render('@Admin/SheetsGroup/pre_create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @throws UserNotAllowedToManageGroupException
     * @throws AccessDeniedException
     */
    public function createAction(Request $request, Event $event, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_OPERATE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        try {
            $this->userToGroupManagerChecker->isUserToGroupManagerAllowed($event, $user);
        } catch (UserAlreadyGroupManagerOnSameEventException $exception) {
            throw $this->createAccessDeniedException('User is not allowed to be manager');
        } catch (UserAlreadyParticipantOrOwnerOnGroupOnSameEventException $exception) {
            throw $this->createAccessDeniedException('User is not allowed to be manager');
        }

        $querySheets = new SheetViewQuery($event, $user, $event->getAvailableLocale($request->getLocale()));
        $sheetViews  = $this->queryBus->handle($querySheets);

        $command = new Create($event, $user);
        $form    = $this->createForm(CreateType::class, $command, ['sheetViews' => $sheetViews]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($command);
            $this->addFlash('success', 'flash.admin.group.create.success');

            return $this->redirectToRoute('admin_sheets_group_list', ['event' => $event->getId()]);
        }

        return $this->render('@Admin/SheetsGroup/create.html.twig', [
            'event' => $event,
            'user'  => $user,
            'form'  => $form->createView(),
        ]);
    }

    public function updateAction(Request $request, Event $event, Group $group): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_OPERATE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new Update($group);
        $form = $this->createForm(UpdateType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->handle($command);
                $this->addFlash('success', 'flash.admin.group.update.success');

                return $this->redirectToRoute('admin_sheets_group_list', ['event' => $event->getId()]);
            } catch (UserNotAllowedToManageGroupException $exception) {
                $this->notifyFormError($form, 'email', 'validators.group.user_not_allowed_to_manage', ['%email%' => $exception->email]);
            } catch (UserNotFoundForGivenEmailException $exception) {
                $this->notifyFormError($form, 'email', 'validators.group.email_not_found', ['%email%' => $exception->email]);
            }
        }

        return $this->render('AdminBundle:SheetsGroup:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    private function notifyFormError(FormInterface $form, string $field, string $translationKey, array $options = []): void
    {
        $translator = $this->translator;

        $form->get($field)->addError(
            new FormError($translator->trans(
                $translationKey,
                $options,
                'validators'
            ))
        );
    }
}
