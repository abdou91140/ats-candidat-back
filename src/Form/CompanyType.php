<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('description', TextType::class, ['label' => 'description'])
            ->add('date', TextType::class, ['label' => 'Date de création'])
            ->add('size', TextType::class, ['label' => 'Nombre de salariés'])
            ->add('members', TextType::class, ['label' => 'Fondateurs(trices)'])
            ->add('url', TextType::class, ['label' => 'Site web'])
            ->add('contacts', CollectionType::class, [
                'entry_type' => ContactType::class, // Assuming ContactType is your form type for Contact entity
                'entry_options' => [
                    'attr' => ['class' => 'text-box'],
                ],
                'allow_add'=>true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
