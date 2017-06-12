<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Group;

use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Common\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class UpdateType extends AbstractGroupType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('email', EmailType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'update_sheets_group';
    }
}
