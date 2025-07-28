<?php

namespace App\Form;

use App\Entity\LostPets;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;

class LostPetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Campos del animal
            ->add('animalName', TextType::class, [
                'label' => 'Nombre del animal',
                'mapped' => false,
                'constraints' => [new NotBlank(message: 'El nombre del animal es obligatorio')],
            ])
            ->add('animalType', ChoiceType::class, [
                'label' => 'Tipo de animal',
                'mapped' => false,
                'choices' => [
                    'Perro' => 'perro',
                    'Gato' => 'gato',
                    'Ave' => 'ave',
                    'Otro' => 'otro',
                ],
                'constraints' => [new NotBlank(message: 'El tipo de animal es obligatorio')],
            ])
            ->add('animalGender', ChoiceType::class, [
                'label' => 'Género',
                'mapped' => false,
                'choices' => [
                    'Macho' => 'male',
                    'Hembra' => 'female',
                    'No especificado' => 'dont_know',
                ],
                'constraints' => [new NotBlank(message: 'El género es obligatorio')],
            ])
            ->add('animalSize', ChoiceType::class, [
                'label' => 'Tamaño',
                'mapped' => false,
                'choices' => [
                    'Pequeño' => 'small',
                    'Mediano' => 'medium',
                    'Grande' => 'large',
                    'Extra Grande' => 'extra_large',
                ],
                'constraints' => [new NotBlank(message: 'El tamaño es obligatorio')],
            ])
            ->add('animalColor', TextType::class, [
                'label' => 'Color',
                'mapped' => false,
                'required' => false,
            ])
            ->add('animalAge', TextType::class, [
                'label' => 'Edad (aproximada)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('animalDescription', TextareaType::class, [
                'label' => 'Descripción',
                'mapped' => false,
                'required' => false,
            ])
            ->add('animalTags', TextType::class, [
                'label' => 'Etiquetas (separadas por comas)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ej: amigable, juguetón, tranquilo, etc.'
                ],
            ])
            ->add('animalPhoto', FileType::class, [
                'label' => 'Fotos del animal',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '2M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp',
                                'image/avif'
                            ],
                            'mimeTypesMessage' => 'Por favor sube una imagen válida (JPG, PNG, GIF, WebP, AVIF)',
                            'maxSizeMessage' => 'La imagen no puede ser mayor a 2MB'
                        ])
                    ])
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'data-max-files' => '5'
                ]
            ])

            // Campos de pérdida
            ->add('lostDate', DateType::class, [
                'label' => 'Fecha de pérdida',
                'widget' => 'single_text',
                'constraints' => [new NotBlank(message: 'La fecha de pérdida es obligatoria')],
            ])
            ->add('lostTime', TimeType::class, [
                'label' => 'Hora aproximada de pérdida',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('lostZone', TextType::class, [
                'label' => 'Zona donde se perdió',
                'constraints' => [new NotBlank(message: 'La zona de pérdida es obligatoria')],
            ])
            ->add('lostAddress', TextType::class, [
                'label' => 'Dirección específica',
                'required' => false,
            ])
            ->add('lostCircumstances', TextareaType::class, [
                'label' => 'Circunstancias de la pérdida',
                'required' => false,
            ])
            ->add('rewardAmount', TextType::class, [
                'label' => 'Monto de recompensa',
                'required' => false,
            ])
            ->add('rewardDescription', TextareaType::class, [
                'label' => 'Descripción de la recompensa',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LostPets::class,
        ]);
    }
}
