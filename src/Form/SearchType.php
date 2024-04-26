<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'label' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ de recherche ne peut pas être vide.',
                    ]),
                    new Regex([
                        'pattern' => '/^(\d{4}-\d{2}-\d{2})|([a-zA-Z\s]+)|(\d+)$/i',
                        'message' => 'Entrez une date valide (YYYY-MM-DD), un nom (lettres et espaces uniquement) ou un ID (composé uniquement de chiffres).'
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'YYYY-MM-DD ou Nom',
                    'class' => 'form-control',
                    'autocomplete' => 'off'
                ],
                
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $form->getData();

            // Vérifiez si le champ de recherche est vide
          

            // Vérifiez si la recherche a donné aucun résultat
            // Insérez ici votre logique pour vérifier si la recherche a donné aucun résultat
            $resultNotFound = false; // Remplacez cela par votre propre logique

            if ($resultNotFound) {
                $form->addError(new FormError('Nous n\'avons pas trouvé cette valeur.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'method' => 'GET',
        ]);
    }
}
