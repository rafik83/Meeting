<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

class BaseController extends Controller
{
    /**
     * @param Form $form
     * @param array $template
     * @param array $data
     *
     * @return Form
     */
    protected function addRequiredErrorOnForm(Form $form, array $template, array $data)
    {
        foreach ($data as $key => $value) {
            if ($template[$key]['required'] === 'true' && $value === null) {
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
}
