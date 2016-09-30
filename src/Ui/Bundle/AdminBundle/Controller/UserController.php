<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\FilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $locale = $event->getAvailableLocale($request->getLocale());

        $typeFilter = [];
        $filterType = $this->createFilterForm(
            FilterType::class,
            $typeFilter,
            ['event' => $event, 'locale' => $request->getLocale(), 'user' => $this->getUser()]
        );

        $filtered = $filterType->handleRequest($request)->isSubmitted() && $filterType->isValid();

        if ($filtered) {
            $typeFilter = $filterType->getData();
        }

        $filterFormView = $filterType->createView();

        $paginatedResult = $this
            ->get('vimeet_infrastructure.repository.user_repository')
            ->paginate(
                $request->query->get('page', 1),
                20,
                $event,
                $typeFilter,
                $locale
            );

        return $this->render('AdminBundle:User:list.html.twig', [
            'event'           => $event,
            'paginatedResult' => $paginatedResult,
            'filter_form'     => $filterFormView,
        ]);
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return Response
     */
    public function showAction(Event $event, User $user)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $userEvent = $this
            ->get('vimeet_infrastructure.repository.user_event_repository')
            ->getUserEvent($user, $event);

        if (null === $userEvent) {
            throw $this->createNotFoundException(
                sprintf(
                    'This user %s is not on this event %s',
                    $user->getId(),
                    $event->getId()
                )
            );
        }

        return $this->render('AdminBundle:User:show.html.twig', [
            'event'  => $event,
            'user'   => $user,
        ]);
    }

    /**
     * @param string $type
     * @param array  $data
     * @param array  $options
     *
     * @return FormInterface
     */
    private function createFilterForm($type, $data, array $options = [])
    {
        return $this->get('form.factory')->createNamed('', $type, $data, array_merge($options, [
            'method'             => 'GET',
            'csrf_protection'    => false,
            'required'           => false,
            'allow_extra_fields' => true,
        ]));
    }
}
