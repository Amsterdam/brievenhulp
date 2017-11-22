<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160518141441 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE hulpverlener_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE hulpverlener (id INT NOT NULL, secret VARCHAR(64) NOT NULL, naam VARCHAR(255) NOT NULL, organisatie VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE hulpvraag (uuid VARCHAR(36) NOT NULL, hulpverlener_id INT DEFAULT NULL, secret VARCHAR(64) NOT NULL, vraag TEXT NOT NULL, methode VARCHAR(8) NOT NULL, email VARCHAR(255) DEFAULT NULL, telefoon VARCHAR(50) DEFAULT NULL, inkomst_datum_tijd TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, start_datum_tijd TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, eind_datum_tijd TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, bestandsnaam VARCHAR(255) DEFAULT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE INDEX IDX_F3B7F1C41E773DB9 ON hulpvraag (hulpverlener_id)');
        $this->addSql('ALTER TABLE hulpvraag ADD CONSTRAINT FK_F3B7F1C41E773DB9 FOREIGN KEY (hulpverlener_id) REFERENCES hulpverlener (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hulpvraag DROP CONSTRAINT FK_F3B7F1C41E773DB9');
        $this->addSql('DROP SEQUENCE hulpverlener_id_seq CASCADE');
        $this->addSql('DROP TABLE hulpverlener');
        $this->addSql('DROP TABLE hulpvraag');
    }
}
