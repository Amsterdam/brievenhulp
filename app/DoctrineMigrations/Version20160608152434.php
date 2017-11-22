<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160608152434 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE audit_log_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE audit_log_entry (id INT NOT NULL, hulpverlener_id INT DEFAULT NULL, hulpvraag_uuid VARCHAR(36) DEFAULT NULL, datumtijd TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, actie VARCHAR(100) NOT NULL, route VARCHAR(255) NOT NULL, ip VARCHAR(50) NOT NULL, referer VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D2D938A21E773DB9 ON audit_log_entry (hulpverlener_id)');
        $this->addSql('CREATE INDEX IDX_D2D938A25195458F ON audit_log_entry (hulpvraag_uuid)');
        $this->addSql('ALTER TABLE audit_log_entry ADD CONSTRAINT FK_D2D938A21E773DB9 FOREIGN KEY (hulpverlener_id) REFERENCES hulpverlener (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE audit_log_entry ADD CONSTRAINT FK_D2D938A25195458F FOREIGN KEY (hulpvraag_uuid) REFERENCES hulpvraag (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE audit_log_entry_id_seq CASCADE');
        $this->addSql('DROP TABLE audit_log_entry');
    }
}
