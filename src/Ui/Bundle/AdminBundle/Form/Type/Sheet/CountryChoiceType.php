<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Application\Query\Sheet\CountryViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\CountryViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\CountryView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryChoiceType extends AbstractType
{
    /** @var CountryViewQueryHandler */
    private $countryViewQueryHandler;

    public function __construct(CountryViewQueryHandler $countryViewQueryHandler)
    {
        $this->countryViewQueryHandler = $countryViewQueryHandler;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setDefaults([
            'choices' => function (Options $options) {
                return $this->countryViewQueryHandler->handle(new CountryViewQuery($options['event'], $options['locale']));
            },
            'choice_label' => function ($countryView) {
                if ($countryView instanceof CountryView) {
                    return $countryView->name;
                }

                return null;
            },
            'choice_value' => function ($countryView) {
                if ($countryView instanceof CountryView) {
                    return $countryView->code;
                }

                return null;
            },
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
