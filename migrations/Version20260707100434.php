<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707100434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ScannerClient (id INT AUTO_INCREMENT NOT NULL, uname VARCHAR(255) NOT NULL, lastOnline DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ScannerLogEntry (id INT AUTO_INCREMENT NOT NULL, level VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, timeStamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, scanner_id INT NOT NULL, INDEX IDX_7B4B93EF67C89E33 (scanner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ScannerLogEntry ADD CONSTRAINT FK_7B4B93EF67C89E33 FOREIGN KEY (scanner_id) REFERENCES ScannerClient (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ScannerLogEntry DROP FOREIGN KEY FK_7B4B93EF67C89E33');
        $this->addSql('DROP TABLE ScannerClient');
        $this->addSql('DROP TABLE ScannerLogEntry');
    }
}
