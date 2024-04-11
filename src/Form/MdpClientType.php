<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MdpClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ancienMotDePasse', PasswordType::class, [
                'label' => 'Ancien mot de passe',
                'mapped' => false, 
            ])
            ->add('motDePasse', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'required' => false, // Pour afficher le champ vide
            ])
            ->add('Modifier', SubmitType::class, [
                'attr' => ['class' => 'btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn', 'id' => 'submitBtn' ,'style' => 'background-color: #56ab2f; border-color:#56ab2f']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
