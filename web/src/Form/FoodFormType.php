<?php

namespace App\Form;

use App\Entity\Food;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Length;

class FoodFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => "Nom : ",
                'attr' => ['class' => 'ml-1 input bg-white border-gray-500'],
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'max' => 125,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité : ',
                'attr' => ['class' => 'ml-1 input bg-white border-gray-500 mt-5'],
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'La quantité doit être au dessus de 0 et en dessous de 100',
                    ]),
                ],
            ])
            ->add('expireDate', DateType::class, [
                'label' => "Date de péremption : ",
                'attr' => ['class' => 'ml-1 input bg-white border-gray-500 mt-5'],
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de péremption doit être égale ou ultérieure à aujourd\'hui.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => "AJOUTER",
                'attr' => ['class' => 'btn btn-primary text-white mt-5 w-52']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Food::class,
        ]);
    }
}
