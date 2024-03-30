<?php

namespace App\Form;

use App\Entity\Rarity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RarityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name'
            ])
            ->add('value', NumberType::class,[
                'label' => 'label.value',

            ])
            ->add('description', TextareaType::class, [
                'label' => 'label.description',
                'required' => false
            ])
            ->add('color', ColorType::class, [
                'label' => 'label.color',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'label.save'
            ])
        ->setAction($options['route']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rarity::class,
            'route' => ''
        ]);
    }
}
