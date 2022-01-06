<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191102103637 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // $this->addSql('CREATE TABLE app_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_BB13C9085E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE role__app_group (app_group_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_79BB0239F6D4B9EA (app_group_id), INDEX IDX_79BB0239D60322AC (role_id), PRIMARY KEY(app_group_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, module VARCHAR(255) DEFAULT \'Sin Modulo\' NOT NULL, UNIQUE INDEX UNIQ_57698A6A5E237E06 (name), UNIQUE INDEX UNIQ_57698A6A2B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) DEFAULT NULL, salt VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, super_admin TINYINT(1) NOT NULL, hash VARCHAR(255) DEFAULT NULL, last_login DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE user__app_group (user_id INT NOT NULL, app_group_id INT NOT NULL, INDEX IDX_51E8AEF9A76ED395 (user_id), INDEX IDX_51E8AEF9F6D4B9EA (app_group_id), PRIMARY KEY(user_id, app_group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        // $this->addSql('ALTER TABLE role__app_group ADD CONSTRAINT FK_79BB0239F6D4B9EA FOREIGN KEY (app_group_id) REFERENCES app_group (id)');
        // $this->addSql('ALTER TABLE role__app_group ADD CONSTRAINT FK_79BB0239D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        // $this->addSql('ALTER TABLE user__app_group ADD CONSTRAINT FK_51E8AEF9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        // $this->addSql('ALTER TABLE user__app_group ADD CONSTRAINT FK_51E8AEF9F6D4B9EA FOREIGN KEY (app_group_id) REFERENCES app_group (id)');
        
        // $this->addSql("INSERT INTO user (username, password, salt, name, last_name, email, enabled, super_admin) VALUES
        //     ('admin','$2y$12$11de78266ffe86b3ccaffuGRnj9p6lpnTtfrHiN6QQ0Yn3/Q0b/WO','11de78266ffe86b3ccaff2f9a75d958c83036016',
        //     'Admin', 'Admin', 'admin@admin.com', 1, 1)");

        // $this->addSql("INSERT INTO role (name, title, description,module) VALUES
        //     ('ROLE_USER_LIST', 'User list', 'Permission to view the system user list','User'),
        //     ('ROLE_USER_NEW', 'New user', 'Permission to add a new user','User'),
        //     ('ROLE_USER_EDIT', 'Edit user', 'Permission to edit a system user','User'),
        //     ('ROLE_USER_VIEW', 'View user', 'Permission to view a system user','User'),
        //     ('ROLE_USER_DELETE', 'Delete user', 'Permission to delete a system user','User'),
        //     ('ROLE_GROUP_LIST', 'Permission groups list', 'Permission to view the permission group list','Permission group'),
        //     ('ROLE_GROUP_NEW', 'New group', 'Permission to add a new permission group','Permission group'),
        //     ('ROLE_GROUP_EDIT', 'Edit group', 'Permission to edit a permission group','Permission group'),
        //     ('ROLE_GROUP_VIEW', 'View group', 'Permission to view a permission group','Permission group'),
        //     ('ROLE_GROUP_DELETE', 'Delete group', 'Permission to delete a permission group','Permission group')
        //     ");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE role__app_group DROP FOREIGN KEY FK_79BB0239F6D4B9EA');
        $this->addSql('ALTER TABLE user__app_group DROP FOREIGN KEY FK_51E8AEF9F6D4B9EA');
        $this->addSql('ALTER TABLE role__app_group DROP FOREIGN KEY FK_79BB0239D60322AC');
        $this->addSql('ALTER TABLE user__app_group DROP FOREIGN KEY FK_51E8AEF9A76ED395');
        $this->addSql('DROP TABLE app_group');
        $this->addSql('DROP TABLE role__app_group');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user__app_group');
    }
}
