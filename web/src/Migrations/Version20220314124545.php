<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220314124545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articulos CHANGE descripcion descripcion VARCHAR(300) NOT NULL, CHANGE precio precio DOUBLE PRECISION NOT NULL, CHANGE ganancia ganancia DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE presupuestos CHANGE fecha fecha DATE DEFAULT NULL, CHANGE total total DOUBLE PRECISION DEFAULT NULL, CHANGE user user VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE presupuestos_art CHANGE id_art id_art INT DEFAULT NULL');
        $this->addSql('ALTER TABLE presupuestos_art ADD CONSTRAINT FK_3B2A193662913E0E FOREIGN KEY (id_art) REFERENCES articulos (id)');
        $this->addSql('CREATE INDEX IDX_3B2A193662913E0E ON presupuestos_art (id_art)');
        $this->addSql('ALTER TABLE stock CHANGE usuario usuario VARCHAR(250) NOT NULL');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656603932A204 FOREIGN KEY (id_articulo) REFERENCES articulos (id)');
        $this->addSql('CREATE INDEX IDX_4B3656603932A204 ON stock (id_articulo)');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY ventas_ibfk_1');
        $this->addSql('DROP INDEX id_cliente ON ventas');
        $this->addSql('CREATE INDEX IDX_808D9E2A813255 ON ventas (id_cliente)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT ventas_ibfk_1 FOREIGN KEY (id_cliente) REFERENCES lista_de_usuarios (ID)');
        $this->addSql('ALTER TABLE ventas_art CHANGE id_ventas id_ventas INT DEFAULT NULL, CHANGE id_art id_art INT DEFAULT NULL, CHANGE total total DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ventas_art ADD CONSTRAINT FK_3EAF49A126B73873 FOREIGN KEY (id_ventas) REFERENCES ventas (id)');
        $this->addSql('ALTER TABLE ventas_art ADD CONSTRAINT FK_3EAF49A162913E0E FOREIGN KEY (id_art) REFERENCES articulos (id)');
        $this->addSql('CREATE INDEX IDX_3EAF49A126B73873 ON ventas_art (id_ventas)');
        $this->addSql('CREATE INDEX IDX_3EAF49A162913E0E ON ventas_art (id_art)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articulos CHANGE descripcion descripcion VARCHAR(300) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE precio precio DOUBLE PRECISION DEFAULT NULL, CHANGE ganancia ganancia DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE presupuestos CHANGE fecha fecha DATE NOT NULL, CHANGE total total DOUBLE PRECISION NOT NULL, CHANGE user user VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`');
        $this->addSql('ALTER TABLE presupuestos_art DROP FOREIGN KEY FK_3B2A193662913E0E');
        $this->addSql('DROP INDEX IDX_3B2A193662913E0E ON presupuestos_art');
        $this->addSql('ALTER TABLE presupuestos_art CHANGE id_art id_art INT NOT NULL');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656603932A204');
        $this->addSql('DROP INDEX IDX_4B3656603932A204 ON stock');
        $this->addSql('ALTER TABLE stock CHANGE usuario usuario VARCHAR(45) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`');
        $this->addSql('ALTER TABLE ventas DROP FOREIGN KEY FK_808D9E2A813255');
        $this->addSql('DROP INDEX idx_808d9e2a813255 ON ventas');
        $this->addSql('CREATE INDEX id_cliente ON ventas (id_cliente)');
        $this->addSql('ALTER TABLE ventas ADD CONSTRAINT FK_808D9E2A813255 FOREIGN KEY (id_cliente) REFERENCES lista_de_usuarios (ID)');
        $this->addSql('ALTER TABLE ventas_art DROP FOREIGN KEY FK_3EAF49A126B73873');
        $this->addSql('ALTER TABLE ventas_art DROP FOREIGN KEY FK_3EAF49A162913E0E');
        $this->addSql('DROP INDEX IDX_3EAF49A126B73873 ON ventas_art');
        $this->addSql('DROP INDEX IDX_3EAF49A162913E0E ON ventas_art');
        $this->addSql('ALTER TABLE ventas_art CHANGE id_ventas id_ventas INT NOT NULL, CHANGE id_art id_art INT NOT NULL, CHANGE total total DOUBLE PRECISION DEFAULT NULL');
    }
}
