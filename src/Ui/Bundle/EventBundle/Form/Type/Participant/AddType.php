<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddType extends AbstractType
{
    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /**
     * @param TemplateDataFactory $templateDataFactory
     */
    public function __construct(TemplateDataFactory $templateDataFactory)
    {
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sheet = $options['sheet'];
        $locale = $options['locale'];
        $products = $options['products'];

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType($sheet->getType(), $locale);
        $identityObjects = $registrationTemplate->getUserIdentityObjects();

        if (isset($identityObjects[Tag::PARTICIPANT_FIRSTNAME])) {
            $firstnameObject = $identityObjects[Tag::PARTICIPANT_FIRSTNAME];

            $builder
                ->add('firstName', TextType::class, [
                    'required'    => $firstnameObject->getRequired(),
                    'label'       => $firstnameObject->getLabel($locale),
                    'constraints' => $firstnameObject->getRequired() ? [new NotBlank()] : [],
                ]);
        }

        if (isset($identityObjects[Tag::PARTICIPANT_LASTNAME])) {
            $lastnameObject = $identityObjects[Tag::PARTICIPANT_LASTNAME];

            $builder
                ->add('lastName', TextType::class, [
                    'required'    => $lastnameObject->getRequired(),
                    'label'       => $lastnameObject->getLabel($locale),
                    'constraints' => $lastnameObject->getRequired() ? [new NotBlank()] : [],
                ]);
        }

        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label'    => 'form.add_participant.children.email.placeholder',
            ]);

        if (!empty($products)) {
            $builder
                ->add('product', ChoiceType::class, [
                    'required'    => true,
                    'choices'     => array_filter($products, function (ParticipantProductView $product = null) {
                        if (null === $product) {
                            return false;
                        }

                        return $product->isBuyable;
                    }),
                    'choice_name' => function (ParticipantProductView $product) {
                        return $product->id;
                    },
                    'expanded'    => true,
                    'multiple'    => false,
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Add::class]);
        $resolver->setRequired(['sheet', 'locale', 'products']);
        $resolver->setAllowedTypes('sheet', Sheet::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'add_participant';
    }
}
