<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => true,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'required' => true,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone',
                'required' => false,
            ])
            ->add('emailNotifications', CheckboxType::class, [
                'label' => 'Email Notifications',
                'required' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active Account',
                'required' => false,
                'data' => true, // Default to active
            ])
            ->add('isShelter', CheckboxType::class, [
                'label' => 'Register as Shelter',
                'required' => false,
            ])
            ->add('shelterName', TextType::class, [
                'label' => 'Shelter Name',
                'required' => false,
            ])
            ->add('shelterDescription', TextareaType::class, [
                'label' => 'Shelter Description',
                'required' => false,
            ])
            ->add('shelterAddress', TextType::class, [
                'label' => 'Shelter Address',
                'required' => false,
            ])
            ->add('shelterPhone', TelType::class, [
                'label' => 'Shelter Phone',
                'required' => false,
            ])
            ->add('shelterWebsite', UrlType::class, [
                'label' => 'Shelter Website',
                'required' => false,
            ])
            ->add('shelterFacebook', UrlType::class, [
                'label' => 'Shelter Facebook',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
