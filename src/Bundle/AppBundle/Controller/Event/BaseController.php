<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

class BaseController extends Controller
{
    /**
     * @param Form  $form
     * @param array $template
     * @param array $data
     *
     * @return Form
     */
    protected function addRequiredErrorOnForm(Form $form, array $template, array $data)
    {
        foreach ($data as $key => $value) {
            if (isset($template[$key])
                && isset($template[$key]['required'])
                && $template[$key]['required'] === true
                && $value === null
            ) {
                $error = new FormError(
                    $this
                        ->get('translator')
                        ->trans('validators.field.required', [], 'validators')
                );
                $form->get('data')->get($key)->addError($error);
            }
        }

        return $form;
    }

    /**
     * @param \Traversable|array $participants
     *
     * @throws AccessDeniedException
     */
    protected function denyAccessForNonParticipant($participants)
    {
        $isUserParticipant = false;

        foreach ($participants as $participant) {
            if ($this->getUser() === $participant->getUser()) {
                $isUserParticipant = true;
            }
        }

        if (!$isUserParticipant) {
            throw new AccessDeniedException('You can not update this data');
        }
    }
}
