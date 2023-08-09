<?php

namespace App\DataFixtures;

use App\Entity\Logs;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $date = new \DateTime();
        for ($i=0; $i < 10; $i++) {
            $log = new Logs();
            $log->setTimeStamp($date);
            $log->setMessage(mt_rand(1, 100));
            $log->setStatus($i%2);
            $manager->persist($log);
        }
        $manager->flush();
    }
}
