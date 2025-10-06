<?php

namespace App\DataFixtures;

use App\Entity\Figure;
use App\Entity\Vitrine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // --- Vitrine: Solo Leveling ---
        $soloLeveling = new Vitrine();
        $soloLeveling->setDescription('Collection Solo Leveling – chasseurs et ombres emblématiques');
        $manager->persist($soloLeveling);
        
        $soloLevelingFigures = [
            "Sung Jin-Woo (Shadow Monarch)",
            "Cha Hae-In (Hunter S-Rank)",
            "Beru (Ant King Shadow)",
        ];
        
        foreach ($soloLevelingFigures as $name) {
            $figure = new Figure();
            $figure->setName($name);
            $figure->setVitrine($soloLeveling);   // ✅ relation
            $manager->persist($figure);
        }
        
        // --- Vitrine: Kaiju No. 8 ---
        $kaiju8 = new Vitrine();
        $kaiju8->setDescription('Collection Kaiju No. 8 – Défense Force et Kaijus');
        $manager->persist($kaiju8);
        
        $kaijuFigures = [
            "Kafka Hibino (Kaiju No. 8)",
            "Mina Ashiro (Commander)",
            "Reno Ichikawa (Rookie Defense Force)",
        ];
        
        foreach ($kaijuFigures as $name) {
            $figure = new Figure();
            $figure->setName($name);
            $figure->setVitrine($kaiju8);     // ✅ relation
            $manager->persist($figure);
        }
        
        $manager->flush();
    }
}
