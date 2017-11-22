<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160616100105 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE hulpverlener SET sms_sjabloon_nederlands = 'Wij hebben u vandaag gebeld. We konden u echter niet bereiken. Vriendelijke groet, Snap de Brief (U kunt niet reageren op dit bericht)'");
        $this->addSql("UPDATE hulpverlener SET sms_sjabloon_engels = 'We called you today. But we couldn''t reach you. Kind regards, Snap de Brief (You cannot reply to this message)'");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
    }
}