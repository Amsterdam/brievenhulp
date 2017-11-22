<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160601161447 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE hulpverlener ADD telefoon VARCHAR(12) NULL');
        $this->addSql('UPDATE hulpverlener SET telefoon = \'\'');
        $this->addSql('ALTER TABLE hulpverlener ALTER COLUMN telefoon SET NOT NULL');

        $this->addSql('ALTER TABLE hulpverlener ADD beschikbaarheid JSON DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hulpverlener DROP telefoon');
        $this->addSql('ALTER TABLE hulpverlener DROP beschikbaarheid');
    }
}
