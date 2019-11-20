<?php

namespace Proximum\Vimeet\Domain\Type\RegistrationPath\View;

class AddQuestion
{
    /**
     * @var array
     * example: ['fr' => 'Ma question', 'en' => 'My question']
     */
    public $translatedTitle = [];

    /** @var Answer[] */
    public $answers = [];
}
