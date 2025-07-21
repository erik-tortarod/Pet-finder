<?php

namespace App\Form;

use App\Entity\Animals;
use App\Entity\FoundAnimals;
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

class EditFoundPetType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options): void
   {
      $builder
         // Campos del animal (mapeados a la entidad Animals)
         ->add('name', TextType::class, [
            'label' => 'Nombre del animal (si se conoce)',
            'required' => false,
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
            'label' => 'Descripción del animal',
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

         // Campos de hallazgo (mapeados a la entidad FoundAnimals)
         ->add('foundDate', DateType::class, [
            'label' => 'Fecha de hallazgo',
            'widget' => 'single_text',
            'mapped' => false,
            'constraints' => [new NotBlank(message: 'La fecha de hallazgo es obligatoria')],
         ])
         ->add('foundTime', TimeType::class, [
            'label' => 'Hora aproximada de hallazgo',
            'widget' => 'single_text',
            'mapped' => false,
            'required' => false,
         ])
         ->add('foundZone', TextType::class, [
            'label' => 'Zona donde se encontró',
            'mapped' => false,
            'constraints' => [new NotBlank(message: 'La zona de hallazgo es obligatoria')],
         ])
         ->add('foundAddress', TextType::class, [
            'label' => 'Dirección específica',
            'mapped' => false,
            'required' => false,
         ])
         ->add('foundCircumstances', TextareaType::class, [
            'label' => 'Circunstancias del hallazgo',
            'mapped' => false,
            'required' => false,
         ])
         ->add('additionalNotes', TextareaType::class, [
            'label' => 'Notas adicionales',
            'mapped' => false,
            'required' => false,
            'attr' => [
               'placeholder' => 'Información adicional sobre el animal o el hallazgo'
            ],
         ])
      ;
   }

   public function configureOptions(OptionsResolver $resolver): void
   {
      $resolver->setDefaults([
         'data_class' => Animals::class,
         'foundAnimal' => null,
      ]);
   }
}
