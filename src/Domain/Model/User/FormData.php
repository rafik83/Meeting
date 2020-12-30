<?php

namespace Proximum\Vimeet\Domain\Model\User;

use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;

class FormData
{
    /** @var User */
    private $user;

    /** @var FormTemplate */
    private $formTemplate;

    /** @var array */
    private $data;

    public function __construct(User $user, FormTemplate $formTemplate, array $data)
    {
        $this->user = $user;
        $this->formTemplate = $formTemplate;
        $this->data = $data;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getFormTemplate(): FormTemplate
    {
        return $this->formTemplate;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function updateData(array $data): void
    {
        $this->data = $data;
    }
}
