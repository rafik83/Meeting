<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class EditableObject extends TemplateObject
{
    /**
     * @return bool
     */
    public function isEditable()
    {
        return true;
    }
}
