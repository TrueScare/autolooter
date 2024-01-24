<?php

namespace App\Form;

use App\Entity\Item;
use App\Entity\Rarity;
use App\Entity\Table;
use PhpParser\Node\Scalar\String_;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label.description',
                'required' => false
            ])
            ->add('value_start', NumberType::class, [
                'label' => 'label.value_from'
            ])
            ->add('value_end', NumberType::class, [
                'label' => 'label.value_to'
            ])
            ->add('parent', EntityType::class, [
                'class' => Table::class,
                'choice_label' => 'name',
                'choices' => $options['tableChoices'],
                'label' => 'label.parent'
            ])
            ->add('rarity', EntityType::class, [
                'class' => Rarity::class,
                'choice_label' => 'name',
                'choices' => $options['rarityChoices'],
                'label' => 'label.rarity'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'label.save',
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
