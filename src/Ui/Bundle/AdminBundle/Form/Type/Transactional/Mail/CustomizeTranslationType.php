<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transactional\Mail;

use Proximum\Vimeet\Ui\Helper\Messaging\MessagePlaceholderHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomizeTranslationType extends AbstractType
{
    /** @var MessagePlaceholderHelper */
    private $placeholderHelper;

    /**
     * @param MessagePlaceholderHelper $placeholderHelper
     */
    public function __construct(MessagePlaceholderHelper $placeholderHelper)
    {
        $this->placeholderHelper = $placeholderHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class)
            ->add('content', TextareaType::class, [
                'attr' => [
                    'data-placeholders' => $options['isCustomizableByType']
                        ? json_encode($this->placeholderHelper->getPlaceholderData())
                        : json_encode($this->placeholderHelper->getGenericPlaceholderData())
                    ,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('isCustomizableByType')
        ;
    }
}
