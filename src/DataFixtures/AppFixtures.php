<?php

namespace App\DataFixtures;

use App\Entity\Member;
use App\Entity\Vitrine;
use App\Entity\Figure;
use App\Entity\Arena;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    
    public function load(ObjectManager $manager): void
    {
        /**
         * Membres (les deux auront un mix des deux animes)
         */
        $membersData = [
            ['key' => 'olivier', 'email' => 'olivier@localhost', 'password' => '123456'],
            ['key' => 'slash',   'email' => 'slash@localhost',   'password' => '123456'],
        ];
        
        foreach ($membersData as $m) {
            $member = new Member();
            $member->setEmail($m['email']);
            $member->setPassword($this->hasher->hashPassword($member, $m['password']));
            $manager->persist($member);
            $this->addReference('member.' . $m['key'], $member);
        }
        
        /**
         * Pool commun de personnages (Kaiju No. 8 + Solo Leveling)
         */
        $characterPool = [
            // Kaiju No. 8
            'Kafka Hibino', 'Mina Ashiro', 'Reno Ichikawa', 'Kikoru Shinomiya', 'Soshiro Hoshina',
            'Isao Shinomiya', 'Kaiju No. 9',
            
            // Solo Leveling
            'Sung Jinwoo', 'Cha Hae-In', 'Yoo Jinho', 'Go Gunhee',
            'Thomas Andre', 'Liu Zhigang', 'Christopher Reed',
            'Beru', 'Igris', 'Bellion',
        ];
        $poolSize = count($characterPool);
        
        /**
         * 1 Vitrine / membre (thème mix)
         */
        foreach ($membersData as $m) {
            /** @var Member $owner */
            $owner = $this->getReference('member.' . $m['key'], Member::class);
            
            $vitrine = new Vitrine();
            $vitrine->setDescription('KaijuHunter Showcase — Mix Kaiju & Hunters (owner: ' . ucfirst($m['key']) . ')');
            $vitrine->setOwner($owner);
            
            $manager->persist($vitrine);
            $this->addReference('vitrine.' . $m['key'], $vitrine);
        }
        
        /**
         * 6 Figures / membre en piochant dans le pool commun (offset différent par membre)
         */
        $nbFiguresPerMember = 6;
        foreach ($membersData as $index => $m) {
            /** @var Vitrine $vitrine */
            $vitrine = $this->getReference('vitrine.' . $m['key'], Vitrine::class);
            
            // offset pour varier les noms entre membres
            $startOffset = ($index * 3) % $poolSize;
            
            for ($i = 0; $i < $nbFiguresPerMember; $i++) {
                $name = $characterPool[($startOffset + $i) % $poolSize];
                
                $fig = new Figure();
                $fig->setName($name);
                $fig->setVitrine($vitrine);
                
                $manager->persist($fig);
                $this->addReference('figure.' . $m['key'] . '.' . ($i + 1), $fig);
            }
        }
        
        /**
         * 2 Arenas / membre (titres mixtes) + liaison de 3 figures chacune
         */
        $arenaNamePairs = [
            // liste de couples (Arena 1, Arena 2) avec ambiance mix
            ['Shibuya Breach Arena', 'Hunter Association Arena'],
            ['Defense Force Proving Grounds', 'Jeju Raid Colosseum'],
            ['Second Division Training Dome', 'Shadow Monarch’s Pit'],
            ['Anti-Kaiju Battle Zone', 'Gate Break Coliseum'],
        ];
        
        foreach ($membersData as $i => $m) {
            /** @var Member $owner */
            $owner = $this->getReference('member.' . $m['key'], Member::class);
            
            $pair = $arenaNamePairs[$i % count($arenaNamePairs)];
            [$name1, $name2] = $pair;
            
            // Arena 1 : publiée = true
            $arena1 = new Arena();
            $arena1->setDescription($name1 . ' — curated by ' . ucfirst($m['key']));
            $arena1->setPublie(true);
            $arena1->setOwner($owner);
            // figures 1..3
            $arena1->addFigure($this->getReference('figure.' . $m['key'] . '.1', Figure::class));
            $arena1->addFigure($this->getReference('figure.' . $m['key'] . '.2', Figure::class));
            $arena1->addFigure($this->getReference('figure.' . $m['key'] . '.3', Figure::class));
            $manager->persist($arena1);
            $this->addReference('arena.' . $m['key'] . '.1', $arena1);
            
            // Arena 2 : publiée = false
            $arena2 = new Arena();
            $arena2->setDescription($name2 . ' — curated by ' . ucfirst($m['key']));
            $arena2->setPublie(false);
            $arena2->setOwner($owner);
            // figures 4..6
            $arena2->addFigure($this->getReference('figure.' . $m['key'] . '.4', Figure::class));
            $arena2->addFigure($this->getReference('figure.' . $m['key'] . '.5', Figure::class));
            $arena2->addFigure($this->getReference('figure.' . $m['key'] . '.6', Figure::class));
            $manager->persist($arena2);
            $this->addReference('arena.' . $m['key'] . '.2', $arena2);
        }
        
        $manager->flush();
    }
}
