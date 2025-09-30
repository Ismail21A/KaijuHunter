<?php

namespace App\DataFixtures;

use App\Entity\Vitrine;
use App\Entity\Figure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    // Internal references for linking Figures -> Vitrines
    private const VITRINE_SOLOLEVELING = 'vitrine-sololeveling';
    private const VITRINE_KAIJU8 = 'vitrine-kaiju8';
    
    public function load(ObjectManager $manager): void
    {
        // -- 1) Vitrines (Anime Universes)
        $vitrines = [
            [self::VITRINE_SOLOLEVELING, "Vitrine Solo Leveling"],
            [self::VITRINE_KAIJU8, "Vitrine Kaiju No. 8"],
        ];
        
        foreach ($vitrines as [$ref, $desc]) {
            $v = new Vitrine();
            $v->setDescription($desc);
            $manager->persist($v);
            $manager->flush(); // to get ID
            $this->addReference($ref, $v);
        }
        
        // -- 2) Figures (Characters / Monsters)
        $figures = [
            [self::VITRINE_SOLOLEVELING, 'Sung Jin-Woo'],
            [self::VITRINE_SOLOLEVELING, 'Cha Hae-In'],
            [self::VITRINE_SOLOLEVELING, 'Beru'],
            
            [self::VITRINE_KAIJU8, 'Kafka Hibino'],
            [self::VITRINE_KAIJU8, 'Kaiju No. 8'],
            [self::VITRINE_KAIJU8, 'Mina Ashiro'],
        ];
        
        foreach ($figures as [$ref, $name]) {
            /** @var Vitrine $vitrine */
            $vitrine = $this->getReference($ref, Vitrine::class);
            
            $f = new Figure();
            $f->setName($name);
            $f->setVitrine($vitrine);
            $manager->persist($f);
        }
        
        $manager->flush();
    }
}
