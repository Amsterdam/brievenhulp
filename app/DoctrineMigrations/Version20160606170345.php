<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160606170345 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE hulpverlener_toewijzing_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE hulpverlener_toewijzing (id INT NOT NULL, hulpverlener_id INT DEFAULT NULL, hulpvraag_uuid VARCHAR(36) DEFAULT NULL, datumtijd TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, info VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DA4C25E91E773DB9 ON hulpverlener_toewijzing (hulpverlener_id)');
        $this->addSql('CREATE INDEX IDX_DA4C25E95195458F ON hulpverlener_toewijzing (hulpvraag_uuid)');
        $this->addSql('ALTER TABLE hulpverlener_toewijzing ADD CONSTRAINT FK_DA4C25E91E773DB9 FOREIGN KEY (hulpverlener_id) REFERENCES hulpverlener (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hulpverlener_toewijzing ADD CONSTRAINT FK_DA4C25E95195458F FOREIGN KEY (hulpvraag_uuid) REFERENCES hulpvraag (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE hulpverlener_toewijzing_id_seq CASCADE');
        $this->addSql('DROP TABLE hulpverlener_toewijzing');
    }
}
