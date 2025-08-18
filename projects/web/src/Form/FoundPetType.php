<?php

namespace App\Form;

use App\Entity\FoundAnimals;
use App\Entity\Tags;
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

class FoundPetType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder, array $options): void
   {
      $builder
         // Campos del animal
         ->add('animalName', TextType::class, [
            'label' => 'Nombre del animal (si se conoce)',
            'mapped' => false,
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
         ->add('animalTypeOther', TextType::class, [
            'label' => 'Especificar tipo de animal',
            'mapped' => false,
            'required' => false,
            'attr' => [
               'placeholder' => 'Ej: conejo, hámster, tortuga, etc.',
               'class' => 'animal-type-other-field',
               'style' => 'display: none;'
            ],
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
            'label' => 'Descripción del animal',
            'mapped' => false,
            'required' => false,
         ])
         ->add('mostUsedTags', ChoiceType::class, [
            'label' => 'Etiquetas más usadas',
            'mapped' => false,
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'choices' => $this->getMostUsedTagsChoices($options['most_used_tags'] ?? []),
            'attr' => [
               'class' => 'most-used-tags-checkboxes'
            ],
         ])
         ->add('animalTags', TextType::class, [
            'label' => 'Otras etiquetas (separadas por comas)',
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

         // Campos de hallazgo
         ->add('foundDate', DateType::class, [
            'label' => 'Fecha de hallazgo',
            'widget' => 'single_text',
            'constraints' => [new NotBlank(message: 'La fecha de hallazgo es obligatoria')],
         ])
         ->add('foundTime', TimeType::class, [
            'label' => 'Hora aproximada de hallazgo',
            'widget' => 'single_text',
            'required' => false,
         ])
         ->add('foundZone', TextType::class, [
            'label' => 'Zona donde se encontró',
            'constraints' => [new NotBlank(message: 'La zona de hallazgo es obligatoria')],
         ])
         ->add('foundAddress', TextType::class, [
            'label' => 'Dirección específica',
            'required' => false,
         ])
         ->add('foundCircumstances', TextareaType::class, [
            'label' => 'Circunstancias del hallazgo',
            'required' => false,
         ])
         ->add('additionalNotes', TextareaType::class, [
            'label' => 'Notas adicionales',
            'required' => false,
            'attr' => [
               'placeholder' => 'Información adicional sobre el animal o el hallazgo'
            ],
         ])
      ;
   }

   private function getMostUsedTagsChoices(array $mostUsedTags): array
   {
      $choices = [];
      foreach ($mostUsedTags as $tag) {
         $choices[$tag->getName()] = $tag->getId();
      }
      return $choices;
   }

   public function configureOptions(OptionsResolver $resolver): void
   {
      $resolver->setDefaults([
         'data_class' => FoundAnimals::class,
         'most_used_tags' => [],
      ]);
   }
}
