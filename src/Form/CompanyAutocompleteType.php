<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Contact;
use App\Repository\CompanyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyAutocompleteType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Startup', EntityType::class, [
                'class' => Company::class,
                'choice_label' => 'name',
                'choice_attr' => function (Company $company) {
                    return ['id' => $company->getId()];
                },
                'label' => false,
                'autocomplete' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
