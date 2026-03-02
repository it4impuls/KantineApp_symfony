<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302133511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE TimeEntry (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, checkinTime DATETIME NOT NULL, checkoutTime DATETIME DEFAULT NULL, INDEX IDX_988949E1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user__fa (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, Department VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_7AA54CFB92FC23A8 (username_canonical), UNIQUE INDEX UNIQ_7AA54CFBA0D96FBF (email_canonical), UNIQUE INDEX UNIQ_7AA54CFBC05FB297 (confirmation_token), UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE TimeEntry ADD CONSTRAINT FK_988949E1A76ED395 FOREIGN KEY (user_id) REFERENCES Costumer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE TimeEntry DROP FOREIGN KEY FK_988949E1A76ED395');
        $this->addSql('DROP TABLE TimeEntry');
        $this->addSql('DROP TABLE user__fa');
    }
}
