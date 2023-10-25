<?php

namespace App\Form;

use App\Entity\Demand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class DemandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('startDate',DateTimeType::class,array('label' => 'Date Début',
                'attr' => [
                    'placeholder' => 'Date début congé',
                    'class' => 'form-control datetimepicker',

                ],
                'html5' => false,
                'label'=> 'Date début congé',
                'widget' => 'single_text',
                'input'  => 'datetime',
                'required' => true,
                'format' => 'dd/MM/yyyy',))


            ->add('endDate',DateType::class,array('label' => 'Date fin du congé',
                'attr' => [
                    'placeholder' => 'Date fin congé',
                    'class' => 'form-control datetimepicker',
                    'dp_language'=>'fr',
                ],
                'html5' => false,
                'label'=> 'Date fin congé',
                'widget' => 'single_text',
                'required' => true,
                'format' => 'dd/MM/yyyy',))

            ->add('reason',TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label'=>'Motif',
                'constraints' => [
                    new Regex(
                        array(
                            'pattern' =>'/^((?!<script)[\s\S])*$/',
                            'message' => 'cette valeur n\'est pas valide ',
                            )
                        ),
                    new Regex(
                        array(
                            'pattern' =>'/^((?!<img)[\s\S])*$/',
                            'message' => 'cette valeur n\'est pas valide ')
                        ),
                ]

            ])

        ->add('supportingDocuments', CollectionType::class, array(
        'entry_type' => DocumentType::class,
        'entry_options' => array('label' => false),
        'allow_add' => true,
        'label' => false,

    ))
    ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Demand::class,
        ]);
    }
}
