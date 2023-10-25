<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Department;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class DepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //dd($options['session']);
        $builder
            ->add('label',null, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Regex(

                        array(
                            'pattern' =>'/^((?!<script)[\s\S])*$/',
                            'message' => 'Le nom n\'est pas valide, merci de saisir un nom valide ',
                        )
                    ),
                    new Regex(
                        array(
                            'pattern' =>'/^((?!<img)[\s\S])*$/',
                            'message' => 'Le nom n\'est pas valide, merci de saisir un nom valide ')
                    ),
                ],
            ])
            ->add('company', EntityType::class, [
                'label' => 'Entreprise',
                'attr' => [
                    'placeholder' => 'Entrprise associé',
                    'class' => 'form-control',

                ],
                'class' => Company::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('c')
                        ->where('c.id= :id')
                        ->setParameter('id', $options['session'])
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Department::class,
        ])
        ->setRequired(array(
            'session'
        ));
    }
}
