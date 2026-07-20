<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260720113104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ScannerLogEntry DROP FOREIGN KEY `FK_7B4B93EF67C89E33`');
        $this->addSql('ALTER TABLE ScannerLogEntry ADD CONSTRAINT FK_7B4B93EF67C89E33 FOREIGN KEY (scanner_id) REFERENCES ScannerClient (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ScannerLogEntry DROP FOREIGN KEY FK_7B4B93EF67C89E33');
        $this->addSql('ALTER TABLE ScannerLogEntry ADD CONSTRAINT `FK_7B4B93EF67C89E33` FOREIGN KEY (scanner_id) REFERENCES ScannerClient (id)');
    }
}
