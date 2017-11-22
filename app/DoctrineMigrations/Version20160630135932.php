<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160630135932 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE hulpvraag DROP CONSTRAINT fk_f3b7f1c4e63c34d6');
        $this->addSql('DROP INDEX uniq_f3b7f1c4e63c34d6');
        $this->addSql('ALTER TABLE hulpvraag DROP referentie_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hulpvraag ADD referentie_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE hulpvraag ADD CONSTRAINT fk_f3b7f1c4e63c34d6 FOREIGN KEY (referentie_id) REFERENCES referentie (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_f3b7f1c4e63c34d6 ON hulpvraag (referentie_id)');
    }
}
