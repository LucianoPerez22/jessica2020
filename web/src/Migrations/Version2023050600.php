<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version2023050600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ventas_recurrentes (id INT AUTO_INCREMENT NOT NULL, id_cliente INT DEFAULT NULL, fecha DATE DEFAULT NULL, total DOUBLE PRECISION DEFAULT NULL, numero INT DEFAULT NULL, cae BIGINT DEFAULT NULL, tipo VARCHAR(2) DEFAULT NULL, caeVenc DATE DEFAULT NULL, user VARCHAR(255) DEFAULT NULL, createdAt DATE DEFAULT NULL, INDEX IDX_CF324F372A813255 (id_cliente), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ventas_recurrentes ADD CONSTRAINT FK_CF324F372A813255 FOREIGN KEY (id_cliente) REFERENCES lista_de_usuarios (ID)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ventas_recurrentes');
    }
}
