<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Unavailability;

use Symfony\Component\Form\FormView;

class CreateFormView
{
    const XML_HTTP_REQUEST_RESPONSE = 'xml_http_request';
    const HANDLER_SUCCESS           = 'handle_success';
    const HANDLER_ERROR             = 'handle_error';
    const CREATE_FORM               = 'create_form';

    /** @var string */
    public $type;

    /** @var FormView */
    public $formView;

    /**
     * @param string   $type
     * @param FormView $formView
     */
    public function __construct(string $type, FormView $formView)
    {
        $this->type = $type;
        $this->formView = $formView;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->type === self::HANDLER_ERROR;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->type === self::HANDLER_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isXmlHttpRequest(): bool
    {
        return $this->type === self::XML_HTTP_REQUEST_RESPONSE;
    }
}
