<?php

namespace App\Form;

use App\Entity\Arena;
use App\Entity\Figure;
use App\Entity\Vitrine;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FigureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', null, [
            'label' => 'Nom de la figure',
        ])
        
        // Vitrine : affichée mais non modifiable
        ->add('vitrine', EntityType::class, [
            'class'        => Vitrine::class,
            'choice_label' => 'id',
            'label'        => 'Vitrine',
            'disabled'     => true,
        ])
        
        // Arènes liées (ManyToMany)
        ->add('arenas', EntityType::class, [
            'class'        => Arena::class,
            'choice_label' => 'id',
            'multiple'     => true,
            'required'     => false,
            'label'        => 'Arènes associées',
            // IMPORTANT: utilise addArena()/removeArena() -> synchronise aussi Arena::figures
            'by_reference' => false,
        ])
        
        // Image upload (non mappé)
        ->add('imageFile', FileType::class, [
            'label'    => 'Image de la figure',
            'mapped'   => false,
            'required' => false,
        ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Figure::class,
        ]);
    }
}
