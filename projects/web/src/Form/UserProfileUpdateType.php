<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserProfileUpdateType extends AbstractType
{
   private UserRepository $userRepository;

   public function __construct(UserRepository $userRepository)
   {
      $this->userRepository = $userRepository;
   }

   public function buildForm(FormBuilderInterface $builder, array $options): void
   {
      $builder
         ->add('firstName', TextType::class, [
            'label' => 'Nombre',
            'required' => true,
            'attr' => [
               'placeholder' => 'Ingresa tu nombre',
               'maxlength' => 255,
            ],
            'constraints' => [
               new Assert\NotBlank([
                  'message' => 'El nombre es obligatorio',
               ]),
               new Assert\Length([
                  'min' => 2,
                  'max' => 255,
                  'minMessage' => 'El nombre debe tener al menos {{ limit }} caracteres',
                  'maxMessage' => 'El nombre no puede tener más de {{ limit }} caracteres',
               ]),
               new Assert\Regex([
                  'pattern' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                  'message' => 'El nombre solo puede contener letras y espacios',
               ]),
            ],
         ])
         ->add('lastName', TextType::class, [
            'label' => 'Apellido',
            'required' => true,
            'attr' => [
               'placeholder' => 'Ingresa tu apellido',
               'maxlength' => 255,
            ],
            'constraints' => [
               new Assert\NotBlank([
                  'message' => 'El apellido es obligatorio',
               ]),
               new Assert\Length([
                  'min' => 2,
                  'max' => 255,
                  'minMessage' => 'El apellido debe tener al menos {{ limit }} caracteres',
                  'maxMessage' => 'El apellido no puede tener más de {{ limit }} caracteres',
               ]),
               new Assert\Regex([
                  'pattern' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                  'message' => 'El apellido solo puede contener letras y espacios',
               ]),
            ],
         ])
         ->add('email', EmailType::class, [
            'label' => 'Email',
            'required' => true,
            'attr' => [
               'placeholder' => 'ejemplo@correo.com',
               'maxlength' => 180,
            ],
            'constraints' => [
               new Assert\NotBlank([
                  'message' => 'El email es obligatorio',
               ]),
               new Assert\Email([
                  'message' => 'El email "{{ value }}" no es válido',
                  'mode' => Assert\Email::VALIDATION_MODE_STRICT,
               ]),
               new Assert\Length([
                  'max' => 180,
                  'maxMessage' => 'El email no puede tener más de {{ limit }} caracteres',
               ]),
            ],
         ])
         ->add('phone', TelType::class, [
            'label' => 'Teléfono',
            'required' => false,
            'attr' => [
               'placeholder' => '+34 123 456 789',
               'maxlength' => 20,
            ],
            'constraints' => [
               new Assert\Length([
                  'max' => 20,
                  'maxMessage' => 'El teléfono no puede tener más de {{ limit }} caracteres',
               ]),
               new Assert\Regex([
                  'pattern' => '/^[\+]?[0-9\s\-\(\)]+$/',
                  'message' => 'El teléfono solo puede contener números, espacios, guiones y paréntesis',
               ]),
            ],
         ])
      ;

      // Agregar validación personalizada para email único
      $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
         $form = $event->getForm();
         $user = $event->getData();

         if (!$user instanceof User) {
            return;
         }

         $email = $form->get('email')->getData();
         $currentUser = $form->getData();

         // Solo validar si el email ha cambiado
         if ($email && $currentUser && $email !== $currentUser->getEmail()) {
            $existingUser = $this->userRepository->findOneBy(['email' => $email]);
            if ($existingUser && $existingUser->getId() !== $currentUser->getId()) {
               $form->get('email')->addError(new \Symfony\Component\Form\FormError('Este email ya está en uso por otro usuario'));
            }
         }
      });
   }

   public function configureOptions(OptionsResolver $resolver): void
   {
      $resolver->setDefaults([
         'data_class' => User::class,
         'csrf_protection' => true,
         'csrf_field_name' => '_token',
         'csrf_token_id' => 'user_profile_update',
      ]);
   }
}
