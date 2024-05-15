<?php

namespace App\Form;

use App\Entity\Rarity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class MoveItemsBetweenRarities extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder
           ->add('rarity', EntityType::class, [
               'class' => Rarity::class,
               'choice_label' => 'name',
               'choices' => $options['rarityChoices'],
               'label' => new TranslatableMessage('rarity.self', domain: 'labels')
           ])
           ->add('submit', SubmitType::class, [
               'label' => new TranslatableMessage('save', domain: 'labels'),
           ])
           ->setAction($options['route']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => Rarity::class,
            'route' => ''
        ]);
    }
}