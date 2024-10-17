<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241017184517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create `users` table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
          id SERIAL NOT NULL,
          email VARCHAR(180) NOT NULL,
          roles JSON NOT NULL,
          password VARCHAR(255) NOT NULL,
          token VARCHAR(255) DEFAULT NULL,
          PRIMARY KEY(id)
        )');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
