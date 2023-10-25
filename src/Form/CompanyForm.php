<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;

class CompanyForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'entreprise',
                'attr' => [
                    'class' => 'form-control'
                ],
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
            ->add('imageFile', FileType::class, [
                'label' => 'Logo',
                'data_class' => null,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Merci de choisir un type correspond a l\'image',
                    ])
                ],
                'empty_data' => '',
                'attr' => [
                    'class' =>'form-control',
                    'placeholder' => 'pièce justificatif',


                ],
            ])
            ->add('adress', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'class' => 'form-control'
                ],
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
            ->add('country', ChoiceType::class, [
                'choices' => [
                    'FR' => ['French' => 'La France'],
                    'TN' => ['Tunisia' => 'La Tunisie'],
                    'BE' => ['Belgium' => 'La Belgique'],
                    'CA' => ['Canada' => 'Le Cananda'],
                    'EE' => ['Estonia' => 'L\'Estonie'],
                    'DE' => ['Germany' => 'L\'Allemagne'],
                    'IE' => ['Ireland' => 'L\'Irlande'],
                    'IL' => ['Israel' => 'Israël'],
                    'IT' => ['Italy' => 'L\'Italie'],
                    'MC' => ['Monaco' => 'Monaco'],
                    'PT' => ['Portugal' => 'Le Portugal'],
                    'RO' => ['Romania' => 'La Roumanie'],
                    'SK' => ['Slovakia' => 'La Slovaquie'],
                    'ES' => ['Spain' => 'L\'Espagne'],
                    'SE' => ['Sweden' => 'La Suède'],
                    'CH' => ['Switzerland' => 'La Suisse'],
                    'UK' => ['United Kingdom of Great Britain and Northern Ireland (the)' => 'Royaume-Uni de Grande-Bretagne et d\'Irlande du Nord (le)'],
                    'US' => ['United States of America (the)' => 'Les États-Unis d\'Amérique '],
                ],
                'label' => 'Pays',
                'attr' => [
                    'class' => 'form-control'
                ],
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
            ->add('zipcode', NumberType::class, [
                'label' => 'Code',
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('phoneNumber', NumberType::class, [
                'label' => 'Code',
                'attr' => [
                    'class' => 'form-control'
                ],
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
            ->add('email', TextType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control'
                ],
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
            ->add('site', TextType::class, [
                'label' => 'Adresse du Site',
                'attr' => [
                    'class' => 'form-control'
                ],
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
            ->add('users', EntityType::class, [
                'label' => 'Responsable pour cet entreprise',
                'attr' => [
                    'placeholder' => 'Admin de l\'entreprise',
                    'class' => 'form-control js-example-basic-multiple',
                    'multiple'=>true,
                    'required' => false,
                    'translation_domain' => 'Default',

                ],
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {

                    return $er->createQueryBuilder('u')
                              ->innerJoin('u.RoleRole', 'r')
                              ->where('r.code = :code')
                              ->setParameter('code', 'ROLE_ADMIN')
                              ->orderBy('u.firstName', 'ASC');
                },
                'choice_label' => 'firstName',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }

}