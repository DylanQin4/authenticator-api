<?php

namespace App\DataFixtures;

//use App\Entity\OldUser;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->userPasswordHasher = $passwordHasher;
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        // Création d'un user "normal"
        $user = new User();
        $user->setEmail("user@bookapi.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "user"));
        $manager->persist($user);

        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@bookapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "admin"));
        $manager->persist($userAdmin);

        // Création des auteurs.
        $listAuthor = [];
        for ($i = 0; $i < 10; $i++) {
            // Création de l'auteur lui-même.
            $author = new Author();
            $author->setFirstName("Prenom " . $i);
            $author->setLastName("Nom " . $i);
            $author->setBiography("Biography ".$i );
            $author->setBirthDate(new \DateTime('1999-'.$i.'-01'));
            $manager->persist($author);

            // On sauvegarde l'auteur créé dans un tableau.
            $listAuthor[] = $author;
        }

        for ($i = 0; $i < 20; $i++) {
            $book = new Book();
            $book->setTitle("Titre " . $i);
            $book->setNumberPages(random_int(10, 200));
            $book->setPublicationDate(null);
            $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $manager->persist($book);
        }
//        // Chargement des données a partir de data.sql
//        $this->loadDataFromSqlFile($manager, '/sql/data.sql');
//
//        // Ajout de nouvelles donnees fixes
//        $admin = new OldUser();
//        $admin->setLastName('admin');
//        $admin->setEmail('admin@example.com');
//        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
//
//        $user1 = new OldUser();
//        $user1->setFirstName('Alice');
//        $user1->setLastName('Smith');
//        $user1->setEmail('alice@example.com');
//        $user1->setPassword($this->passwordHasher->hashPassword($user1, '123'));
//
//        $manager->persist($admin);
//        $manager->persist($user1);

        $manager->flush();
    }

//    private function loadDataFromSqlFile(ObjectManager $manager, string $sqlFile): void
//    {
//        $projectRoot = realpath(__DIR__ . '/../../');
//        $fullPath = $projectRoot . $sqlFile;
//
//        if (file_exists($fullPath)) {
//            $conn = $manager->getConnection();
//            $sql = file_get_contents($fullPath);
//            $conn->exec($sql);
//        } else {
//            throw new \RuntimeException("Le fichier SQL $fullPath n'existe pas.");
//        }
//    }
}
