<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatetimeDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dateTime = $options['object'];
        $locale = $options['locale'];

        $builder
            ->add('datetime', DateTimePickerType::class, [
                'label' => $dateTime->getOption('label', $locale),
                'required' => $dateTime->getOption('required'),
                'help' => $dateTime->getOption('help')[$locale],
                'translation_domain' => false,
                'display_hour' => $dateTime->displayHours(),
                'format' => $dateTime->getDatepickerFormat(),
                'view_timezone' => $dateTime->getTimezone(),
                'min_date' => $dateTime->getOptionDateFormattedForDatepicker('datetime_min'),
                'max_date' => $dateTime->getOptionDateFormattedForDatepicker('datetime_max'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['object', 'locale'])
            ->setAllowedTypes('object', TemplateObject\DateTime::class)
            ->setDefaults([
                'data_class' => TemplateObject\DateTime::class,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'datetime_data';
    }
}
