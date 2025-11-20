<?php

namespace App\Form;

use App\Entity\Arena;
use App\Entity\Figure;
use App\Entity\Member;
use App\Repository\FigureRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArenaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Arena|null $arena */
        $arena = $options['data'] ?? null;
        $owner = $arena?->getOwner();
        
        $builder
        ->add('description')
        ->add('publie')
        
        ->add('owner', EntityType::class, [
            'class'        => Member::class,
            'choice_label' => 'email', 
            'disabled'     => true,    
        ])
        
        ->add('figures', EntityType::class, [
            'class'         => Figure::class,
            'choice_label'  => 'name',
            'multiple'      => true,
            'expanded'      => true,   
            'by_reference'  => false,  
            
            'query_builder' => function (FigureRepository $fr) use ($owner) {
            $qb = $fr->createQueryBuilder('f');
            
            if ($owner !== null) {
                $qb
                ->leftJoin('f.vitrine', 'v')
                ->leftJoin('v.owner', 'm')
                ->andWhere('m = :owner')
                ->setParameter('owner', $owner);
            } else {
                $qb->where('1 = 0');
            }
            
            return $qb;
            },
            ])
            ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Arena::class,
        ]);
    }
}
