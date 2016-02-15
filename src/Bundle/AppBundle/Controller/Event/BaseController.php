<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Exception\Package\BoughtParticipantAlreadyAddedException;
use Proximum\Vimeet\Application\Exception\Package\ForgotToAddQuantityException;
use Proximum\Vimeet\Application\Exception\Package\PackageException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class BaseController extends Controller
{
    /**
     * @param PackageException $exception
     * @param Form             $form
     * @param array            $template
     * @param array            $data
     * @param FormInterface    $formData
     *
     * @return Form
     */
    protected function addErrorOnForm(PackageException $exception, Form $form, array $template, array $data, FormInterface $formData)
    {
        if ($exception instanceof BoughtParticipantAlreadyAddedException) {
            foreach ($data as $key => $value) {
                if (isset($template['template'][$key]) && isset($value['participant']) && $value['participant'] === false) {
                    $formData->get($key)->addError(new FormError('validators.option.participant.optionCanNotBeUnselected'));
                } elseif (isset($template['template'][$key]) && isset($value['participant']) && $value['participant'] === true) {
                    $formData->get($key)->addError(new FormError('validators.option.participant.alreadyAddedBoughtParticipant'));
                }
            }
        } elseif ($exception instanceof ForgotToAddQuantityException) {
            foreach ($data as $key => $value) {
                if (isset($template['template'][$key]) && isset($value['participant']) && $value['participant'] === true && (!isset($value['quantity']) || (isset($value['quantity']) && $value['quantity'] === 0))) {
                    $formData->get($key)->get('participant')->addError(new FormError('validators.option.quantityMustBeAdded'));
                } elseif (isset($template['template'][$key]) && isset($value['planning']) && $value['planning'] === true && (!isset($value['quantity']) || (isset($value['quantity']) && $value['quantity'] === 0))) {
                    $formData->get($key)->get('planning')->addError(new FormError('validators.option.quantityMustBeAdded'));
                }
            }
        }

        return $form;
    }

    protected function addErrorOnFormPackage(PackageException $exception, array $data, Form $form)
    {
        if ($exception instanceof BoughtParticipantAlreadyAddedException) {
            foreach ($data as $stepKey => $step) {
                foreach ($step as $productKey => $product) {
                    if (isset($product['participant'])) {
                        $form->get('packageData')->get($stepKey)->get($productKey)->get('participant')->addError(new FormError('validators.option.participant.optionCanNotBeUnselected'));
                    } elseif (isset($product['participant']) && $product['participant'] === false) {
                        $form->get('packageData')->get($stepKey)->get($productKey)->get('participant')->addError(new FormError('validators.option.participant.alreadyAddedBoughtParticipant'));
                    }
                }
            }
        } elseif ($exception instanceof ForgotToAddQuantityException) {
            foreach ($data as $stepKey => $step) {
                foreach ($step as $productKey => $product) {
                    if (isset($product['planning']) && $product['planning'] === true && (!isset($product['quantity']) || (isset($product['quantity']) && $product['quantity'] === 0))) {
                        $form->get('packageData')->get($stepKey)->get($productKey)->get('planning')->addError(new FormError('validators.option.quantityMustBeAdded'));
                    } elseif (isset($product['participant']) && $product['participant'] === true && (!isset($product['quantity']) || (isset($product['quantity']) && $product['quantity'] === 0))) {
                        $form->get('packageData')->get($stepKey)->get($productKey)->get('participant')->addError(new FormError('validators.option.quantityMustBeAdded'));
                    }
                }
            }
        }
    }
}
