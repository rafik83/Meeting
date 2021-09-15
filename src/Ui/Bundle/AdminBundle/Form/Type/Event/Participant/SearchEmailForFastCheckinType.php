<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Participant;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchEmailForFastCheckinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'email',
            EmailType::class,
            [
                'required' => false,
                'help' => 'form.search_email_for_fast_checkin.children.email.help',
            ]
        )
            ->add('submit', SubmitType::class)
        ;
    }
}
