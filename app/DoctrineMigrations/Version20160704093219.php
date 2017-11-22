<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160704093219 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE hulpvraag ADD archief BOOLEAN');
        $this->addSql('UPDATE hulpvraag SET archief = false');
        $this->addSql('UPDATE hulpvraag SET archief = true WHERE status = 4');
        $this->addSql('UPDATE hulpvraag SET status = 2 WHERE uuid IN (SELECT hulpvraag_uuid FROM verzonden_sms_bericht) AND status = 4');
        $this->addSql('UPDATE hulpvraag SET status = 1 WHERE status = 4');
        $this->addSql('ALTER TABLE hulpvraag ALTER COLUMN archief SET NOT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('UPDATE hulpvraag SET status = 4 WHERE archief = true');
        $this->addSql('ALTER TABLE hulpvraag DROP archief');

    }
}
