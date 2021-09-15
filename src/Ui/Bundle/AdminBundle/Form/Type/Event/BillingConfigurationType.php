<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Application\Command\Event\BillingConfiguration;
use Proximum\Vimeet\Domain\Event\Image;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\BillingConfiguration\TranslationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class BillingConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationType::class,
                'label'      => false,
            ])
            ->add('invoiceLogo', FileType::class, [
                'required' => false,
                'attr'     => [
                    'accept' => implode(', ', Image::SUPPORTED_MIME_TYPE),
                ],
                'constraints' => [
                    new File(
                        [
                            'mimeTypes' => Image::SUPPORTED_MIME_TYPE,
                            'mimeTypesMessage' => 'validators.field.notValid.uploadObject',
                        ]
                    ),
                ],
            ])
            ->add('legalInfo', TextType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = ucfirst(Intl::getLocaleBundle()->getLocaleName($translation->vars['name']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillingConfiguration::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'event_billing_configuration';
    }
}
