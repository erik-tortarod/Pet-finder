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
            'label' => 'animal.edit.form.name_optional',
            'required' => false,
         ])
         ->add('animalType', ChoiceType::class, [
            'label' => 'animal.edit.form.animal_type',
            'mapped' => false,
            'choices' => [
               'animal.edit.form.animal_types.dog' => 'perro',
               'animal.edit.form.animal_types.cat' => 'gato',
               'animal.edit.form.animal_types.bird' => 'ave',
               'animal.edit.form.animal_types.other' => 'otro',
            ],
            'constraints' => [new NotBlank(message: 'animal.edit.validation.animal_type_required')],
         ])
         ->add('gender', ChoiceType::class, [
            'label' => 'animal.edit.form.gender',
            'choices' => [
               'animal.edit.form.gender_types.male' => 'male',
               'animal.edit.form.gender_types.female' => 'female',
               'animal.edit.form.gender_types.unspecified' => 'dont_know',
            ],
            'constraints' => [new NotBlank(message: 'animal.edit.validation.gender_required')],
         ])
         ->add('size', ChoiceType::class, [
            'label' => 'animal.edit.form.size',
            'choices' => [
               'animal.edit.form.size_types.small' => 'small',
               'animal.edit.form.size_types.medium' => 'medium',
               'animal.edit.form.size_types.large' => 'large',
               'animal.edit.form.size_types.extra_large' => 'extra_large',
            ],
            'constraints' => [new NotBlank(message: 'animal.edit.validation.size_required')],
         ])
         ->add('color', TextType::class, [
            'label' => 'animal.edit.form.color',
            'required' => false,
         ])
         ->add('age', TextType::class, [
            'label' => 'animal.edit.form.age',
            'required' => false,
         ])
         ->add('description', TextareaType::class, [
            'label' => 'animal.edit.form.description',
            'required' => false,
         ])
         ->add('animalTags', TextType::class, [
            'label' => 'animal.edit.form.animal_tags',
            'mapped' => false,
            'required' => false,
            'attr' => [
               'placeholder' => 'animal.edit.form.animal_tags_placeholder'
            ],
         ])
         ->add('animalPhoto', FileType::class, [
            'label' => 'animal.edit.form.animal_photo',
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
                  'mimeTypesMessage' => 'animal.edit.validation.invalid_image',
                  'maxSizeMessage' => 'animal.edit.validation.image_too_large'
               ])
            ],
            'attr' => [
               'accept' => 'image/*',
               'class' => 'form-control'
            ]
         ])

         // Campos de hallazgo (mapeados a la entidad FoundAnimals)
         ->add('foundDate', DateType::class, [
            'label' => 'animal.edit.form.found_date',
            'widget' => 'single_text',
            'mapped' => false,
            'constraints' => [new NotBlank(message: 'animal.edit.validation.found_date_required')],
         ])
         ->add('foundTime', TimeType::class, [
            'label' => 'animal.edit.form.found_time',
            'widget' => 'single_text',
            'mapped' => false,
            'required' => false,
         ])
         ->add('foundZone', TextType::class, [
            'label' => 'animal.edit.form.found_zone',
            'mapped' => false,
            'constraints' => [new NotBlank(message: 'animal.edit.validation.found_zone_required')],
         ])
         ->add('foundAddress', TextType::class, [
            'label' => 'animal.edit.form.found_address',
            'mapped' => false,
            'required' => false,
         ])
         ->add('foundCircumstances', TextareaType::class, [
            'label' => 'animal.edit.form.found_circumstances',
            'mapped' => false,
            'required' => false,
         ])
         ->add('additionalNotes', TextareaType::class, [
            'label' => 'animal.edit.form.additional_notes',
            'mapped' => false,
            'required' => false,
            'attr' => [
               'placeholder' => 'animal.edit.form.additional_notes_placeholder'
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
