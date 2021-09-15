<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Transformer\DateTimeToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimePickerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new DateTimeToStringTransformer(
             $options['format'],
             $options['model_timezone'],
             $options['view_timezone']
         ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['data-datatimepicker'] = $this->fixLocale($options['locale']);
        $view->vars['attr']['autocomplete']        = 'off';
        $view->vars['group_attr']['style']         = 'position: relative;';
        $view->vars['attr']['data-allow-hours']    = $options['display_hour'];
        $view->vars['attr']['data-allow-dates']    = $options['display_date'];
        $view->vars['attr']['data-min-date']       = $options['min_date'];
        $view->vars['attr']['data-max-date']       = $options['max_date'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'locale'         => 'fr',
            'format'         => 'd/m/Y H:i',
            'display_hour'   => true,
            'display_date'   => true,
            'model_timezone' => date_default_timezone_get(),
            'view_timezone'  => date_default_timezone_get(),
            'min_date'       => null,
            'max_date'       => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'datetimepicker';
    }

    /**
     * @param $locale
     *
     * @return string
     */
    private function fixLocale($locale)
    {
        $mapping = ['fr' => 'fr', 'en' => 'en-gb'];

        return isset($mapping[$locale]) ? $mapping[$locale] : $mapping['fr'];
    }
}
