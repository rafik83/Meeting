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
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BaseController extends Controller
{
    /**
     * @param Form  $form
     * @param array $template
     * @param array $data
     * @param $formData
     *
     * @return Form
     */
    protected function addRequiredErrorOnForm(Form $form, array $template, array $data, $formData)
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
                $formData->get($key)->addError($error);
            }
        }

        return $form;
    }

    /**
     * @param PackageException $exception
     * @param Form             $form
     * @param array            $template
     * @param array            $data
     * @param $formData
     *
     * @return Form
     */
    protected function addErrorOnForm(PackageException $exception, Form $form, array $template, array $data, $formData)
    {
        if ($exception instanceof BoughtParticipantAlreadyAddedException) {
            foreach ($data as $key => $value) {
                if (isset($template['template'][$key])
                    && isset($value['participant'])
                    && $value['participant'] === false
                ) {
                    $error = new FormError(
                        $this
                            ->get('translator')
                            ->trans('validators.option.participant.optionCanNotBeUnselected', [], 'validators')
                    );
                    $formData->get($key)->addError($error);
                } elseif (isset($template['template'][$key])
                    && isset($value['participant'])
                    && $value['participant'] === true
                ) {
                    $error = new FormError(
                        $this
                            ->get('translator')
                            ->trans('validators.option.participant.alreadyAddedBoughtParticipant', [], 'validators')
                    );
                    $formData->get($key)->addError($error);
                }
            }
        } elseif ($exception instanceof ForgotToAddQuantityException) {
            foreach ($data as $key => $value) {
                if (isset($template['template'][$key])
                    && isset($value['participant'])
                    && $value['participant'] === true
                    && (!isset($value['quantity']) || (isset($value['quantity']) && $value['quantity'] === 0))
                ) {
                    $error = new FormError(
                        $this
                            ->get('translator')
                            ->trans('validators.option.quantityMustBeAdded', [], 'validators')
                    );
                    $formData->get($key)->get('participant')->addError($error);
                } elseif (isset($template['template'][$key])
                    && isset($value['planning'])
                    && $value['planning'] === true
                    && (!isset($value['quantity']) || (isset($value['quantity']) && $value['quantity'] === 0))
                ) {
                    $error = new FormError(
                        $this
                            ->get('translator')
                            ->trans('validators.option.quantityMustBeAdded', [], 'validators')
                    );
                    $formData->get($key)->get('planning')->addError($error);
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
                        $error = new FormError(
                            $this
                                ->get('translator')
                                ->trans('validators.option.participant.optionCanNotBeUnselected', [], 'validators')
                        );
                        $form->get('packageData')->get($stepKey)->get($productKey)->get('participant')->addError($error);
                    } elseif (isset($product['participant']) && $product['participant'] === false) {
                        $error = new FormError(
                            $this
                                ->get('translator')
                                ->trans('validators.option.participant.alreadyAddedBoughtParticipant', [], 'validators')
                        );
                        $form->get('packageData')->get($stepKey)->get($productKey)->get('participant')->addError($error);
                    }
                }
            }
        } elseif ($exception instanceof ForgotToAddQuantityException) {
            foreach ($data as $stepKey => $step) {
                foreach ($step as $productKey => $product) {
                    if (isset($product['planning'])
                        && $product['planning'] === true
                        && (!isset($product['quantity']) || (isset($product['quantity']) && $product['quantity'] === 0))
                    ) {
                        $error = new FormError(
                            $this
                                ->get('translator')
                                ->trans('validators.option.quantityMustBeAdded', [], 'validators')
                        );
                        $form->get('packageData')->get($stepKey)->get($productKey)->get('planning')->addError($error);
                    } elseif (isset($product['participant'])
                        && $product['participant'] === true
                        && (!isset($product['quantity']) || (isset($product['quantity']) && $product['quantity'] === 0))
                    ) {
                        $error = new FormError(
                            $this
                                ->get('translator')
                                ->trans('validators.option.quantityMustBeAdded', [], 'validators')
                        );
                        $form->get('packageData')->get($stepKey)->get($productKey)->get('participant')->addError($error);
                    }
                }
            }
        }
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

    /**
     * @param $error
     * @param $field
     */
    protected function addGivenErrorOnGivenField($error, $field)
    {
        $error = new FormError($error);
        $field->addError($error);
    }

    /**
     * Authenticate user.
     *
     * @param User $user
     */
    protected function authenticate(User $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    /**
     * @param Request $request
     */
    protected function disconnect(Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
        }
    }
}
