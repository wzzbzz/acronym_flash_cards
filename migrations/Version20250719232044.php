<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250719232044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE acronym ADD og_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE acronym ADD og_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE acronym ADD og_image_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE acronym DROP og_title');
        $this->addSql('ALTER TABLE acronym DROP og_description');
        $this->addSql('ALTER TABLE acronym DROP og_image_url');
    }
}
