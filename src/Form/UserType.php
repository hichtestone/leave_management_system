<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

           // ->add('email')

            /*->add('roles', EntityType::class, array(
                'class' => Role::class,
                'choice_label' => 'code', 'multiple' => false
            ))*/
            //->add('imageFile', FileType::class, [
              //  'required' => true
          //  ]);
/*
            ->add('solde')
            ->add('password',PasswordType::class)
            ->add('phoneNumber')
            ->add('firstName')
            ->add('lastName');*/

$builder
    ->add('imageFile', FileType::class, [
        'data_class' => null,
        'attr' => [
        'class' =>'file-upload-input',
        'data-file' => '',
        'onchange'=> 'readURL(this);',


],
]);
}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
