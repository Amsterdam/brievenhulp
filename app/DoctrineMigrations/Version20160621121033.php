<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160621121033 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE referentie_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE referentie (id INT NOT NULL, hulpvraag_uuid VARCHAR(36) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DE1662345195458F ON referentie (hulpvraag_uuid)');
        $this->addSql('ALTER TABLE referentie ADD CONSTRAINT FK_DE1662345195458F FOREIGN KEY (hulpvraag_uuid) REFERENCES hulpvraag (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hulpvraag ADD referentie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE hulpvraag ADD CONSTRAINT FK_F3B7F1C4E63C34D6 FOREIGN KEY (referentie_id) REFERENCES referentie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F3B7F1C4E63C34D6 ON hulpvraag (referentie_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hulpvraag DROP CONSTRAINT FK_F3B7F1C4E63C34D6');
        $this->addSql('DROP SEQUENCE referentie_id_seq CASCADE');
        $this->addSql('DROP TABLE referentie');
        $this->addSql('DROP INDEX UNIQ_F3B7F1C4E63C34D6');
        $this->addSql('ALTER TABLE hulpvraag DROP referentie_id');
    }
}