<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160531112300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE hulpvraag ADD interface_taal VARCHAR(2) NULL');
        $this->addSql('UPDATE hulpvraag SET interface_taal = \'nl\'');
        $this->addSql('ALTER TABLE hulpvraag ALTER COLUMN interface_taal SET NOT NULL');
        $this->addSql('ALTER TABLE hulpvraag ADD vraag_volgens_hulpverlener TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE hulpvraag ADD reactie_van_hulpverlener TEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hulpvraag DROP interface_taal');
        $this->addSql('ALTER TABLE hulpvraag DROP vraag_volgens_hulpverlener');
        $this->addSql('ALTER TABLE hulpvraag DROP reactie_van_hulpverlener');
    }
}
