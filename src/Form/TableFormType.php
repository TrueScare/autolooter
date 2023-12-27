<?php

namespace App\Form;

use App\Entity\Rarity;
use App\Entity\Table;
use App\Entity\User;
use App\Repository\TableRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name'
            ])
            ->add('description', TextareaType::class,[
                'label' => 'label.description'
            ])
            ->add('parent', EntityType::class, [
                'class' => Table::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'label.parent_placeholder',
                'choices' => $options['tableChoices'],
                'label' => 'label.parent'
            ])
            ->add('rarity', EntityType::class, [
                'class' => Rarity::class,
                'choice_label' => 'name',
                'choices' => $options['rarityChoices'],
                'label' => 'label.rarity'
            ])
            ->add('submit', SubmitType::class,[
                'label' => 'label.save'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Table::class,
            'tableChoices' => Table::class,
            'rarityChoices' => Rarity::class
        ]);
    }
}
