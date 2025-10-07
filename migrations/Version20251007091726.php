<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007091726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398E62B9E85');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398E62B9E85 FOREIGN KEY (Costumer_id) REFERENCES Costumer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398E62B9E85');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398E62B9E85 FOREIGN KEY (Costumer_id) REFERENCES Costumer (id) ON DELETE CASCADE');
    }
}
