<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160706111815 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE afzender (naam VARCHAR(125) NOT NULL, PRIMARY KEY(naam))');
        $this->addSql('CREATE TABLE hulpvraag_tag (hulpvraag_uuid VARCHAR(36) NOT NULL, tag_naam VARCHAR(125) NOT NULL, PRIMARY KEY(hulpvraag_uuid, tag_naam))');
        $this->addSql('CREATE INDEX IDX_8CFB55165195458F ON hulpvraag_tag (hulpvraag_uuid)');
        $this->addSql('CREATE INDEX IDX_8CFB55161242068E ON hulpvraag_tag (tag_naam)');
        $this->addSql('CREATE TABLE tag (naam VARCHAR(125) NOT NULL, PRIMARY KEY(naam))');
        $this->addSql('ALTER TABLE hulpvraag_tag ADD CONSTRAINT FK_8CFB55165195458F FOREIGN KEY (hulpvraag_uuid) REFERENCES hulpvraag (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hulpvraag_tag ADD CONSTRAINT FK_8CFB55161242068E FOREIGN KEY (tag_naam) REFERENCES tag (naam) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hulpvraag ADD afzender_naam VARCHAR(125) DEFAULT NULL');
        $this->addSql('ALTER TABLE hulpvraag ADD CONSTRAINT FK_F3B7F1C48134C33 FOREIGN KEY (afzender_naam) REFERENCES afzender (naam) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F3B7F1C48134C33 ON hulpvraag (afzender_naam)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hulpvraag DROP CONSTRAINT FK_F3B7F1C48134C33');
        $this->addSql('ALTER TABLE hulpvraag_tag DROP CONSTRAINT FK_8CFB55161242068E');
        $this->addSql('DROP TABLE afzender');
        $this->addSql('DROP TABLE hulpvraag_tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP INDEX IDX_F3B7F1C48134C33');
        $this->addSql('ALTER TABLE hulpvraag DROP afzender_naam');
    }
}
