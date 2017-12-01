<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone;

use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Symfony\Component\Form\FormInterface;

class SendCodeView
{
    const SEND_CODE_SUCCESS = 'send_code_success';
    const SEND_CODE_SHOW_FORM = 'send_code_show_form';
    const SEND_CODE_FORM_NOT_SHOWN = 'send_code_form_not_shown';

    /** @var string */
    public $status;

    /** @var null|FormInterface */
    public $form;

    /** @var TipTranslationView[] */
    public $tipTranslationViews;

    /** @var null|string */
    public $ignorePhoneConfirmationUrl;

    /**
     * @param string             $status
     * @param FormInterface|null $form
     * @param array              $tipTranslationViews
     * @param null|string        $ignorePhoneConfirmationUrl
     */
    public function __construct(
        $status,
        FormInterface $form = null,
        array $tipTranslationViews = [],
        ?string $ignorePhoneConfirmationUrl = null
    ) {
        if (!in_array($status, [self::SEND_CODE_SUCCESS, self::SEND_CODE_SHOW_FORM, self::SEND_CODE_FORM_NOT_SHOWN])) {
            throw new \InvalidArgumentException('Status value is invalid');
        }

        $this->status = $status;
        $this->form = $form;
        $this->tipTranslationViews = $tipTranslationViews;
        $this->ignorePhoneConfirmationUrl = $ignorePhoneConfirmationUrl;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return self::SEND_CODE_SUCCESS === $this->status;
    }
}
