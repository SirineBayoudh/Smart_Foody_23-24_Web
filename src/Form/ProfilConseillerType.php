<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfilConseillerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('genre', ChoiceType::class, [
                'choices' => 
                [
                    'Femme' => 'Femme',
                    'Homme' => 'Homme',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'champ obligatoire'])
                ]
            ])
            ->add('email')
            ->add('numTel', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]])
            ->add('matricule', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]])
            ->add('attestation', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]])
            ->add('photo')
            ->add('Modifier', SubmitType::class, [
                'attr' => ['class' => 'btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn', 'id' => 'submitBtn' ,'style' => 'background-color: #56ab2f; border-color:#56ab2f; width:180px']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class
        ]);
    }
}
