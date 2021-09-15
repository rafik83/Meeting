<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgendaConfirmedStatusChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'form.sheet_filter.children.agendaConfirmedStatus.choice.all_confirmed' => Sheet::AGENDA_ALL_CONFIRMED,
                'form.sheet_filter.children.agendaConfirmedStatus.choice.partly_confirmed' => Sheet::AGENDA_PARTLY_CONFIRMED,
                'form.sheet_filter.children.agendaConfirmedStatus.choice.none_confirmed' => Sheet::AGENDA_NONE_CONFIRMED,
            ],
            'placeholder' => 'form.sheet_filter.children.agendaConfirmedStatus.choice.all',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
