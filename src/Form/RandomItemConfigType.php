<?php

namespace App\Form;

use App\Entity\Rarity;
use App\Entity\Table;
use App\Struct\RandomItemConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\GreaterThan;

class RandomItemConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('unique_tables', CheckboxType::class, [
                'required' => false,
                'label' => new TranslatableMessage('unique.items', domain: 'labels')
            ])
            ->add('amount', NumberType::class,[
                'required' => true,
                'label' => new TranslatableMessage('amount.items', domain: 'labels'),
                'constraints' => [
                    new GreaterThan([
                        'value' => 0
                        ])
                ]
            ])
            ->add('tables', ChoiceType::class, [
                'required' => false,
                'choices' => $options['tableChoices'],
                'choice_label' => 'name',
                'choice_value' => function(?Table $table): int{
                  return $table ? $table->getId(): '';
                },
                'multiple' => true,
                'label' => new TranslatableMessage('choice.tables', domain: 'labels')
            ])
            ->add('rarities', ChoiceType::class , [
                'required' => false,
                'choices' =>  $options['rarityChoices'],
                'choice_label' => 'name',
                'choice_value' => function(?Rarity $rarity): int{
                return $rarity ? $rarity->getId(): '';
                },
                'multiple' => true,
                'label' => new TranslatableMessage('choice.itemRarities', domain: 'labels')
            ])
            ->add('submit', SubmitType::class, [
                'label'=> new TranslatableMessage('item.random', domain: 'labels')
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RandomItemConfig::class,
            'tableChoices' => Table::class,
            'rarityChoices' => Rarity::class,
        ]);
    }
}