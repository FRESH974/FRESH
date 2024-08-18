<?php

namespace App\Form;

use App\Entity\FoodRecipeNotInRefrigerator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class FoodRecipeNotInRefrigeratorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité : ',
                'attr' => ['placeholder' => 'Quantité', 'class' => 'input border-gray-500 text-black bg-white mb-5'],
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'La quantité doit être un nombre positif ou égal à zéro.',
                    ]),
                    new NotBlank([
                        'message' => 'La quantité est obligatoire.',
                    ]),
                ],
            ])
            ->add('unit', TextType::class, [
                'label' => 'Unité : ',
                'attr' => ['placeholder' => 'Litre', 'class' => 'input border-gray-500 text-black bg-white mb-5'],
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom : ',
                'attr' => ['placeholder' => 'Lait', 'class' => 'input border-gray-500 text-black bg-white mb-5'],
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 125,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                    new NotBlank([
                        'message' => 'Le nom est obligatoire.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FoodRecipeNotInRefrigerator::class,
        ]);
    }
}
