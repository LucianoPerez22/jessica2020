<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220104124503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE articulos ADD id_marca_id INT NOT NULL, DROP id_marca');
        // $this->addSql('ALTER TABLE articulos ADD CONSTRAINT FK_9C6F8597D134ECD4 FOREIGN KEY (id_marca_id) REFERENCES marcas (id)');
        // $this->addSql('CREATE INDEX IDX_9C6F8597D134ECD4 ON articulos (id_marca_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articulos DROP FOREIGN KEY FK_9C6F8597D134ECD4');
        $this->addSql('DROP INDEX IDX_9C6F8597D134ECD4 ON articulos');
        $this->addSql('ALTER TABLE articulos ADD id_marca INT DEFAULT NULL, DROP id_marca_id');
    }
}
