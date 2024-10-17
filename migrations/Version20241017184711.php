<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241017184711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create `comments` table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE comments (
          id SERIAL NOT NULL,
          author_id INT DEFAULT NULL,
          post_id INT DEFAULT NULL,
          content TEXT NOT NULL,
          created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
          updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
          deleted_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
          PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX IDX_5F9E962AF675F31B ON comments (author_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962A4B89032C ON comments (post_id)');

        $this->addSql('COMMENT ON COLUMN comments.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN comments.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN comments.deleted_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('ALTER TABLE comments
            ADD CONSTRAINT FK_5F9E962AF675F31B 
                FOREIGN KEY (author_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );

        $this->addSql('ALTER TABLE comments
            ADD CONSTRAINT FK_5F9E962A4B89032C 
                FOREIGN KEY (post_id) REFERENCES posts (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962AF675F31B');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A4B89032C');

        $this->addSql('DROP TABLE comments');
    }
}
