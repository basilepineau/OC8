<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250817103050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Associe les tâches orphelines à l’utilisateur porteur de ROLE_ANONYME (créé si absent).';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();
        $this->abortIf(!\in_array($platform, ['mysql', 'mariadb'], true), 'Migration prévue pour MySQL/MariaDB.');

        $anonymousId = $this->connection->fetchOne(
            "SELECT id FROM `user` WHERE JSON_VALID(roles) AND JSON_CONTAINS(roles, '\"ROLE_ANONYME\"') = 1 LIMIT 1"
        );

        if ($anonymousId === false) {
            $anonymousId = $this->connection->fetchOne(
                "SELECT id FROM `user` WHERE roles LIKE :like LIMIT 1",
                ['like' => '%ROLE_ANONYME%']
            );
        }

        if ($anonymousId === false) {
            $random = bin2hex(random_bytes(16));
            $hashed = password_hash($random, PASSWORD_BCRYPT);

            $this->addSql(
                "INSERT INTO `user` (username, email, password, roles)
                 VALUES ('anonyme', 'anonyme@example.com', :pwd, JSON_ARRAY('ROLE_ANONYME'))",
                ['pwd' => $hashed]
            );

            $anonymousId = (int) $this->connection->lastInsertId();
        } else {
            $anonymousId = (int) $anonymousId;
        }

        $this->addSql(
            "UPDATE `task` SET user_id = :anonId WHERE user_id IS NULL",
            ['anonId' => $anonymousId]
        );
    }

    public function down(Schema $schema): void
    {
        $anonymousId = $this->connection->fetchOne(
            "SELECT id FROM `user` WHERE JSON_VALID(roles) AND JSON_CONTAINS(roles, '\"ROLE_ANONYME\"') = 1 LIMIT 1"
        );

        if ($anonymousId === false) {
            $anonymousId = $this->connection->fetchOne(
                "SELECT id FROM `user` WHERE roles LIKE :like LIMIT 1",
                ['like' => '%ROLE_ANONYME%']
            );
        }

        if ($anonymousId !== false) {
            $this->addSql(
                "UPDATE `task` SET user_id = NULL WHERE user_id = :anonId",
                ['anonId' => (int) $anonymousId]
            );
        }
    }
}
