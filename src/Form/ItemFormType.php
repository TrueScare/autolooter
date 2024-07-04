<?php

namespace App\Form;

use App\Entity\Item;
use App\Entity\Rarity;
use App\Entity\Table;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class ItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => new TranslatableMessage('name', domain: 'labels')
            ])
            ->add('description', TextareaType::class, [
                'label' => new TranslatableMessage('description', domain: 'labels'),
                'required' => false
            ])
            ->add('value_start', NumberType::class, [
                'label' => new TranslatableMessage('value_from', domain: 'labels')
            ])
            ->add('value_end', NumberType::class, [
                'label' => new TranslatableMessage('value_to', domain: 'labels'),
                'required' => false
            ])
            ->add('parent', EntityType::class, [
                'class' => Table::class,
                'choice_label' => 'name',
                'choices' => $options['tableChoices'],
                'label' => new TranslatableMessage('parent.self', domain: 'labels')
            ])
            ->add('rarity', EntityType::class, [
                'class' => Rarity::class,
                'choice_label' => 'name',
                'choices' => $options['rarityChoices'],
                'label' => new TranslatableMessage('rarity.self', domain: 'labels'),
                'placeholder' => new TranslatableMessage('placeholder', domain: 'labels'),
                'required' => true
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('save', domain: 'labels'),
            ])
            ->setAction($options['route']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
            'tableChoices' => Table::class,
            'rarityChoices' => Rarity::class,
            'route' => ''
        ]);
    }
}
