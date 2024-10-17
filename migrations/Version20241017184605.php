<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241017184605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create `posts` table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE posts (
          id SERIAL NOT NULL,
          author_id INT NOT NULL,
          slug VARCHAR(255) NOT NULL,
          title VARCHAR(255) NOT NULL,
          content TEXT NOT NULL,
          created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
          deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
          PRIMARY KEY(id)
        )');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_885DBAFA989D9B62 ON posts (slug)');

        $this->addSql('CREATE INDEX IDX_885DBAFAF675F31B ON posts (author_id)');

        $this->addSql('COMMENT ON COLUMN posts.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN posts.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN posts.deleted_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('ALTER TABLE posts
            ADD CONSTRAINT FK_885DBAFAF675F31B 
                FOREIGN KEY (author_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE posts DROP CONSTRAINT FK_885DBAFAF675F31B');

        $this->addSql('DROP TABLE posts');
    }
}
