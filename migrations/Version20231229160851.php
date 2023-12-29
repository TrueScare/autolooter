<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231229160851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, rarity_id INT DEFAULT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, value_start DOUBLE PRECISION NOT NULL, value_end DOUBLE PRECISION NOT NULL, INDEX IDX_1F1B251E727ACA70 (parent_id), INDEX IDX_1F1B251EF3747573 (rarity_id), INDEX IDX_1F1B251E7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rarity (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, value INT NOT NULL, color VARCHAR(10) DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_B7C0BE467E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `table` (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, parent_id INT DEFAULT NULL, rarity_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_F6298F467E3C61F9 (owner_id), INDEX IDX_F6298F46727ACA70 (parent_id), INDEX IDX_F6298F46F3747573 (rarity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E727ACA70 FOREIGN KEY (parent_id) REFERENCES `table` (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EF3747573 FOREIGN KEY (rarity_id) REFERENCES rarity (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rarity ADD CONSTRAINT FK_B7C0BE467E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `table` ADD CONSTRAINT FK_F6298F467E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `table` ADD CONSTRAINT FK_F6298F46727ACA70 FOREIGN KEY (parent_id) REFERENCES `table` (id)');
        $this->addSql('ALTER TABLE `table` ADD CONSTRAINT FK_F6298F46F3747573 FOREIGN KEY (rarity_id) REFERENCES rarity (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E727ACA70');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EF3747573');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E7E3C61F9');
        $this->addSql('ALTER TABLE rarity DROP FOREIGN KEY FK_B7C0BE467E3C61F9');
        $this->addSql('ALTER TABLE `table` DROP FOREIGN KEY FK_F6298F467E3C61F9');
        $this->addSql('ALTER TABLE `table` DROP FOREIGN KEY FK_F6298F46727ACA70');
        $this->addSql('ALTER TABLE `table` DROP FOREIGN KEY FK_F6298F46F3747573');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE rarity');
        $this->addSql('DROP TABLE `table`');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
