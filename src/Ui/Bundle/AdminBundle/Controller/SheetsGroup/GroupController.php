<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\SheetsGroup;

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
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Group\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Group\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GroupController extends Controller
{
    /**
     * Search user by email to pre-populate the real create form
     *
     * @param Request $request
     * @param Event   $event
     *
     * @return Response|RedirectResponse
     */
    public function preCreateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_OPERATE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $searchUser = new SearchUser($event);
        $form       = $this->createForm(SearchType::class, $searchUser, ['event' => $event]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $userView = $this->get('tactician.commandbus')->handle($searchUser);

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
     * @param Request $request
     * @param Event   $event
     * @param User    $user
     *
     * @throws UserNotAllowedToManageGroupException
     * @throws AccessDeniedException
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event, User $user)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_OPERATE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        try {
            $this->get('user_to_group_manager_checker')->isUserToGroupManagerAllowed($event, $user);
        } catch (UserAlreadyGroupManagerOnSameEventException $exception) {
            throw $this->createAccessDeniedException('User is not allowed to be manager');
        } catch (UserAlreadyParticipantOrOwnerOnGroupOnSameEventException $exception) {
            throw $this->createAccessDeniedException('User is not allowed to be manager');
        }

        $querySheets = new SheetViewQuery($event, $user, $event->getAvailableLocale($request->getLocale()));
        $sheetViews  = $this->get('tactician.commandbus')->handle($querySheets);

        $command = new Create($event, $user);
        $form    = $this->createForm(CreateType::class, $command, ['sheetViews' => $sheetViews]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.admin.group.create.success');

            return $this->redirectToRoute('admin_sheets_group_list', ['event' => $event->getId()]);
        }

        return $this->render('@Admin/SheetsGroup/create.html.twig', [
            'event' => $event,
            'user'  => $user,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Group   $group
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, Group $group)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_OPERATE');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($event !== $group->getEvent()) {
            throw new AccessDeniedException();
        }

        $command = new Update($group);
        $form = $this->createForm(UpdateType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($command);
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

    /**
     * @param FormInterface $form
     * @param string        $field
     * @param string        $translationKey
     * @param array         $options
     */
    private function notifyFormError(FormInterface $form, $field, $translationKey, array $options = [])
    {
        $translator = $this->get('translator');

        $form->get($field)->addError(
            new FormError($translator->trans(
                $translationKey,
                $options,
                'validators'
            ))
        );
    }
}
