<?php

namespace App\Form;

use App\Entity\Rarity;
use App\Entity\Table;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('owner', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
            ->add('parent', EntityType::class, [
                'class' => Table::class,
'choice_label' => 'id',
            ])
            ->add('rarity', EntityType::class, [
                'class' => Rarity::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Table::class,
        ]);
    }
}
