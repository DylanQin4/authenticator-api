<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;
use Doctrine\DBAL\Connection;

class AppFixtures extends Fixture
{

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        // Charger le fichier SQL
        $sqlFile = __DIR__ . '/../../sql/views.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);

            // Exécuter le SQL (chaque commande séparée par un `;`)
            foreach (explode(';', $sql) as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $this->connection->executeStatement($statement);
                }
            }
        } else {
            throw new \RuntimeException("Le fichier SQL pour les vues n'a pas été trouvé : $sqlFile");
        }

        $manager->flush();
    }

}
