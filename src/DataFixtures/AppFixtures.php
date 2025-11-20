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
         * Membres
         * - admin@localhost   : ADMIN (aucune vitrine/arena/figure)
         * - olivier@localhost : USER
         * - slash@localhost   : USER
         */
        $membersData = [
            [
                'key'      => 'admin',
                'email'    => 'admin@localhost',
                'password' => 'admin123',
                'roles'    => ['ROLE_ADMIN'],
            ],
            [
                'key'      => 'olivier',
                'email'    => 'olivier@localhost',
                'password' => '123456',
                'roles'    => ['ROLE_USER'],
            ],
            [
                'key'      => 'slash',
                'email'    => 'slash@localhost',
                'password' => '123456',
                'roles'    => ['ROLE_USER'],
            ],
        ];
        
        foreach ($membersData as $m) {
            $member = new Member();
            $member->setEmail($m['email']);
            $member->setPassword(
                $this->hasher->hashPassword($member, $m['password'])
                );
            $member->setRoles($m['roles']);
            
            $manager->persist($member);
            $this->addReference('member.' . $m['key'], $member);
        }
        
        /**
         * Pool commun de personnages
         */
        $characterPool = [
            'Kafka Hibino', 'Mina Ashiro', 'Reno Ichikawa', 'Kikoru Shinomiya', 'Soshiro Hoshina',
            'Isao Shinomiya', 'Kaiju No. 9',
            
            'Sung Jinwoo', 'Cha Hae-In', 'Yoo Jinho', 'Go Gunhee',
            'Thomas Andre', 'Liu Zhigang', 'Christopher Reed',
            'Beru', 'Igris', 'Bellion',
        ];
        $poolSize = count($characterPool);
        
        /**
         * On ne crée vitrines/figures/arenas QUE pour ces deux-là
         */
        $collectorKeys = ['olivier', 'slash'];
        
        /**
         * 1 Vitrine / membre (pour olivier & slash)
         */
        foreach ($collectorKeys as $key) {
            /** @var Member $owner */
            $owner = $this->getReference('member.' . $key, Member::class);
            
            $vitrine = new Vitrine();
            $vitrine->setDescription('KaijuHunter Showcase — (owner: ' . ucfirst($key) . ')');
            $vitrine->setOwner($owner);
            
            $manager->persist($vitrine);
            $this->addReference('vitrine.' . $key, $vitrine);
        }
        
        /**
         * 6 Figures / membre
         */
        $nbFiguresPerMember = 6;
        foreach ($collectorKeys as $index => $key) {
            /** @var Vitrine $vitrine */
            $vitrine = $this->getReference('vitrine.' . $key, Vitrine::class);
            
            $startOffset = ($index * 3) % $poolSize;
            
            for ($i = 0; $i < $nbFiguresPerMember; $i++) {
                $name = $characterPool[($startOffset + $i) % $poolSize];
                
                $fig = new Figure();
                $fig->setName($name);
                $fig->setVitrine($vitrine);
                
                $manager->persist($fig);
                $this->addReference('figure.' . $key . '.' . ($i + 1), $fig);
            }
        }
        
        /**
         * 2 Arenas / membre (publiée + privée) pour olivier & slash
         */
        $arenaNamePairs = [
            ['Shibuya Breach Arena', 'Hunter Association Arena'],
            ['Defense Force Proving Grounds', 'Jeju Raid Colosseum'],
        ];
        
        foreach ($collectorKeys as $i => $key) {
            /** @var Member $owner */
            $owner = $this->getReference('member.' . $key, Member::class);
            $pair = $arenaNamePairs[$i % count($arenaNamePairs)];
            [$name1, $name2] = $pair;
            
            $arena1 = new Arena();
            $arena1->setDescription($name1 . ' — curated by ' . ucfirst($key));
            $arena1->setPublie(true);
            $arena1->setOwner($owner);
            $arena1->addFigure($this->getReference('figure.' . $key . '.1', Figure::class));
            $arena1->addFigure($this->getReference('figure.' . $key . '.2', Figure::class));
            $arena1->addFigure($this->getReference('figure.' . $key . '.3', Figure::class));
            $manager->persist($arena1);
            
            $arena2 = new Arena();
            $arena2->setDescription($name2 . ' — curated by ' . ucfirst($key));
            $arena2->setPublie(false);
            $arena2->setOwner($owner);
            $arena2->addFigure($this->getReference('figure.' . $key . '.4', Figure::class));
            $arena2->addFigure($this->getReference('figure.' . $key . '.5', Figure::class));
            $arena2->addFigure($this->getReference('figure.' . $key . '.6', Figure::class));
            $manager->persist($arena2);
        }
        
        $manager->flush();
    }
}