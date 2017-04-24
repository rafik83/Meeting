<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Group;

class CreateType extends AbstractGroupType
{
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'create_sheets_group';
    }
}
