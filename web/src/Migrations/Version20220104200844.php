<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220104200844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articulos ADD CONSTRAINT FK_9C6F8597E98F4023 FOREIGN KEY (id_marca) REFERENCES marcas (id)');
        $this->addSql('CREATE INDEX IDX_9C6F8597E98F4023 ON articulos (id_marca)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articulos DROP FOREIGN KEY FK_9C6F8597E98F4023');
        $this->addSql('DROP INDEX IDX_9C6F8597E98F4023 ON articulos');
    }
}
