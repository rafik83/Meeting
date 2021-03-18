<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\MeetingRequest;

use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\Import;
use Proximum\Vimeet\Domain\MimeType\MimeType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature\CharsetChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class, [
                'help' => 'form.meeting_request_import.children.file.help',
                'constraints' => [
                    new NotBlank(),
                    new File(
                        [
                            'mimeTypes' => MimeType::CSV_MIME_TYPES,
                            'mimeTypesMessage' => 'validators.field.notValid.uploadObject',
                        ]
                    ),
                ]
            ])
            ->add('charset', CharsetChoiceType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'form.meeting_request_import.children.submit.label',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Import::class,
        ]);
    }
}
