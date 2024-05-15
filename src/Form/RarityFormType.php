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
use Symfony\Component\Translation\TranslatableMessage;

class RarityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => new TranslatableMessage('name', domain: 'labels')
            ])
            ->add('value', NumberType::class,[
                'label' => new TranslatableMessage('value', domain: 'labels'),

            ])
            ->add('description', TextareaType::class, [
                'label' => new TranslatableMessage('description', domain: 'labels'),
                'required' => false
            ])
            ->add('color', ColorType::class, [
                'label' => new TranslatableMessage('color', domain: 'labels'),
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('save', domain: 'labels')
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
