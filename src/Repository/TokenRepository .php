<?php

namespace App\Repository;

use App\Entity\Token;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Token>
 */
class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    /**
     * @throws Exception
     */
    public function isValidToken(string $token): ?Token
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT * FROM token_valide WHERE token = :token AND expirated_at > :now LIMIT 1';
        $resultSet = $conn->executeQuery($sql, [
            'token' => $token,
            'now' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        ]);

        $result = $resultSet->fetchAllAssociative();

        return $result ? $this->getEntityManager()->getRepository(Token::class)->find($result['id']) : null;
    }

//    /**
//     * @return Token[] Returns an array of Token objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Token
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}