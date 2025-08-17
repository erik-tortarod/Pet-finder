<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserProfileUpdateType extends AbstractType
{
    private UserRepository $userRepository;
    private TranslatorInterface $translator;

    public function __construct(UserRepository $userRepository, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => $this->translator->trans('user.settings.form.first_name'),
                'required' => true,
                'attr' => [
                    'placeholder' => $this->translator->trans('user.settings.form.first_name_placeholder'),
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => $this->translator->trans('user.settings.validation.first_name_required'),
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => $this->translator->trans('user.settings.validation.first_name_min_length'),
                        'maxMessage' => $this->translator->trans('user.settings.validation.first_name_max_length'),
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                        'message' => $this->translator->trans('user.settings.validation.first_name_invalid'),
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => $this->translator->trans('user.settings.form.last_name'),
                'required' => true,
                'attr' => [
                    'placeholder' => $this->translator->trans('user.settings.form.last_name_placeholder'),
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => $this->translator->trans('user.settings.validation.last_name_required'),
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => $this->translator->trans('user.settings.validation.last_name_min_length'),
                        'maxMessage' => $this->translator->trans('user.settings.validation.last_name_max_length'),
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                        'message' => $this->translator->trans('user.settings.validation.last_name_invalid'),
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('user.settings.form.email'),
                'required' => true,
                'attr' => [
                    'placeholder' => $this->translator->trans('user.settings.form.email_placeholder'),
                    'maxlength' => 180,
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => $this->translator->trans('user.settings.validation.email_required'),
                    ]),
                    new Assert\Email([
                        'message' => $this->translator->trans('user.settings.validation.email_invalid'),
                        'mode' => Assert\Email::VALIDATION_MODE_STRICT,
                    ]),
                    new Assert\Length([
                        'max' => 180,
                        'maxMessage' => $this->translator->trans('user.settings.validation.email_max_length'),
                    ]),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => $this->translator->trans('user.settings.form.phone'),
                'required' => false,
                'attr' => [
                    'placeholder' => $this->translator->trans('user.settings.form.phone_placeholder'),
                    'maxlength' => 20,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 20,
                        'maxMessage' => $this->translator->trans('user.settings.validation.phone_max_length'),
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[\+]?[0-9\s\-\(\)]+$/',
                        'message' => $this->translator->trans('user.settings.validation.phone_invalid'),
                    ]),
                ],
            ])
        ;

        // Agregar campos específicos de shelter si el usuario es una protectora
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user && $user->isShelter()) {
                $form
                    ->add('shelterName', TextType::class, [
                        'label' => $this->translator->trans('user.settings.form.shelter_name'),
                        'required' => true,
                        'attr' => [
                            'placeholder' => $this->translator->trans('user.settings.form.shelter_name_placeholder'),
                            'maxlength' => 255,
                        ],
                        'constraints' => [
                            new Assert\NotBlank([
                                'message' => $this->translator->trans('user.settings.validation.shelter_name_required'),
                            ]),
                            new Assert\Length([
                                'min' => 2,
                                'max' => 255,
                                'minMessage' => $this->translator->trans('user.settings.validation.shelter_name_min_length'),
                                'maxMessage' => $this->translator->trans('user.settings.validation.shelter_name_max_length'),
                            ]),
                        ],
                    ])
                    ->add('shelterDescription', TextareaType::class, [
                        'label' => $this->translator->trans('user.settings.form.shelter_description'),
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->translator->trans('user.settings.form.shelter_description_placeholder'),
                            'rows' => 4,
                            'maxlength' => 500,
                        ],
                        'constraints' => [
                            new Assert\Length([
                                'max' => 500,
                                'maxMessage' => $this->translator->trans('user.settings.validation.shelter_description_max_length'),
                            ]),
                        ],
                    ])
                    ->add('shelterAddress', TextType::class, [
                        'label' => $this->translator->trans('user.settings.form.shelter_address'),
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->translator->trans('user.settings.form.shelter_address_placeholder'),
                            'maxlength' => 255,
                        ],
                        'constraints' => [
                            new Assert\Length([
                                'max' => 255,
                                'maxMessage' => $this->translator->trans('user.settings.validation.shelter_address_max_length'),
                            ]),
                        ],
                    ])
                    ->add('shelterPhone', TelType::class, [
                        'label' => $this->translator->trans('user.settings.form.shelter_phone'),
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->translator->trans('user.settings.form.shelter_phone_placeholder'),
                            'maxlength' => 255,
                        ],
                        'constraints' => [
                            new Assert\Length([
                                'max' => 255,
                                'maxMessage' => $this->translator->trans('user.settings.validation.shelter_phone_max_length'),
                            ]),
                            new Assert\Regex([
                                'pattern' => '/^[\+]?[0-9\s\-\(\)]+$/',
                                'message' => $this->translator->trans('user.settings.validation.shelter_phone_invalid'),
                            ]),
                        ],
                    ])
                    ->add('shelterWebsite', UrlType::class, [
                        'label' => $this->translator->trans('user.settings.form.shelter_website'),
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->translator->trans('user.settings.form.shelter_website_placeholder'),
                            'maxlength' => 255,
                        ],
                        'constraints' => [
                            new Assert\Length([
                                'max' => 255,
                                'maxMessage' => $this->translator->trans('user.settings.validation.shelter_website_max_length'),
                            ]),
                            new Assert\Url([
                                'message' => $this->translator->trans('user.settings.validation.shelter_website_invalid'),
                            ]),
                        ],
                    ])
                    ->add('shelterFacebook', UrlType::class, [
                        'label' => $this->translator->trans('user.settings.form.shelter_facebook'),
                        'required' => false,
                        'attr' => [
                            'placeholder' => $this->translator->trans('user.settings.form.shelter_facebook_placeholder'),
                            'maxlength' => 255,
                        ],
                        'constraints' => [
                            new Assert\Length([
                                'max' => 255,
                                'maxMessage' => $this->translator->trans('user.settings.validation.shelter_facebook_max_length'),
                            ]),
                            new Assert\Url([
                                'message' => $this->translator->trans('user.settings.validation.shelter_facebook_invalid'),
                            ]),
                        ],
                    ])
                ;
            }
        });

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
                    $form->get('email')->addError(new \Symfony\Component\Form\FormError($this->translator->trans('user.settings.validation.email_already_used')));
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
