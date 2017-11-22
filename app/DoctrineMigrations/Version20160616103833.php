<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160616103833 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE hulpverlener ADD sms_sjabloon TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE hulpverlener DROP sms_sjabloon_nederlands');
        $this->addSql('ALTER TABLE hulpverlener DROP sms_sjabloon_engels');
        $this->addSql("UPDATE hulpverlener SET sms_sjabloon = 'Wij hebben u vandaag gebeld. We konden u echter niet bereiken. Vriendelijke groet, Snap de Brief (U kunt niet reageren op dit bericht). We called you today. But we couldn''t reach you. Kind regards, Snap de Brief (You cannot reply to this message)'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hulpverlener ADD sms_sjabloon_engels TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE hulpverlener RENAME COLUMN sms_sjabloon TO sms_sjabloon_nederlands');
    }
}