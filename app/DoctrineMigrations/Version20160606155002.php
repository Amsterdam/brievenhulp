<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160606155002 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE verzonden_sms_bericht_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE verzonden_sms_bericht (id INT NOT NULL, hulpvraag_uuid VARCHAR(36) NOT NULL, hulpverlener_uuid INT NOT NULL, datumtijd TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, bericht VARCHAR(130) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AD8FED925195458F ON verzonden_sms_bericht (hulpvraag_uuid)');
        $this->addSql('CREATE INDEX IDX_AD8FED92D57D01A7 ON verzonden_sms_bericht (hulpverlener_uuid)');
        $this->addSql('ALTER TABLE verzonden_sms_bericht ADD CONSTRAINT FK_AD8FED925195458F FOREIGN KEY (hulpvraag_uuid) REFERENCES hulpvraag (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE verzonden_sms_bericht ADD CONSTRAINT FK_AD8FED92D57D01A7 FOREIGN KEY (hulpverlener_uuid) REFERENCES hulpverlener (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE verzonden_sms_bericht_id_seq CASCADE');
        $this->addSql('DROP TABLE verzonden_sms_bericht');
    }
}
