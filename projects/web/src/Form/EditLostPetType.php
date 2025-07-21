<?php

namespace App\Form;

use App\Entity\Animals;
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

class EditLostPetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Campos del animal (mapeados a la entidad Animals)
            ->add('name', TextType::class, [
                'label' => 'Nombre del animal',
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
            ->add('gender', ChoiceType::class, [
                'label' => 'Género',
                'choices' => [
                    'Macho' => 'male',
                    'Hembra' => 'female',
                    'No especificado' => 'dont_know',
                ],
                'constraints' => [new NotBlank(message: 'El género es obligatorio')],
            ])
            ->add('size', ChoiceType::class, [
                'label' => 'Tamaño',
                'choices' => [
                    'Pequeño' => 'small',
                    'Mediano' => 'medium',
                    'Grande' => 'large',
                    'Extra Grande' => 'extra_large',
                ],
                'constraints' => [new NotBlank(message: 'El tamaño es obligatorio')],
            ])
            ->add('color', TextType::class, [
                'label' => 'Color',
                'required' => false,
            ])
            ->add('age', TextType::class, [
                'label' => 'Edad (aproximada)',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
            ])
            ->add('animalTags', TextType::class, [
                'label' => 'Etiquetas (separadas por comas)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ej: amigable, juguetón, tranquilo'
                ],
            ])
            ->add('animalPhoto', FileType::class, [
                'label' => 'Nueva foto del animal (opcional)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
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
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control'
                ]
            ])

            // Campos de pérdida (mapeados a la entidad LostPets)
            ->add('lostDate', DateType::class, [
                'label' => 'Fecha de pérdida',
                'widget' => 'single_text',
                'mapped' => false,
                'constraints' => [new NotBlank(message: 'La fecha de pérdida es obligatoria')],
            ])
            ->add('lostTime', TimeType::class, [
                'label' => 'Hora aproximada de pérdida',
                'widget' => 'single_text',
                'mapped' => false,
                'required' => false,
            ])
            ->add('lostZone', TextType::class, [
                'label' => 'Zona donde se perdió',
                'mapped' => false,
                'constraints' => [new NotBlank(message: 'La zona de pérdida es obligatoria')],
            ])
            ->add('lostAddress', TextType::class, [
                'label' => 'Dirección específica',
                'mapped' => false,
                'required' => false,
            ])
            ->add('lostCircumstances', TextareaType::class, [
                'label' => 'Circunstancias de la pérdida',
                'mapped' => false,
                'required' => false,
            ])
            ->add('rewardAmount', TextType::class, [
                'label' => 'Monto de recompensa',
                'mapped' => false,
                'required' => false,
            ])
            ->add('rewardDescription', TextareaType::class, [
                'label' => 'Descripción de la recompensa',
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animals::class,
            'lostPet' => null,
        ]);
    }
}
