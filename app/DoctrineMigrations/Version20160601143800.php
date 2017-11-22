<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160601143800 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE hulpverlener ADD password VARCHAR(64) NULL');
        $this->addSql('UPDATE hulpverlener SET password=\'\'');
        $this->addSql('ALTER TABLE hulpverlener ALTER COLUMN password SET NOT NULL');

        $this->addSql('ALTER TABLE hulpverlener ADD is_active BOOLEAN');
        $this->addSql('UPDATE hulpverlener SET is_active=true');
        $this->addSql('ALTER TABLE hulpverlener ALTER COLUMN is_active SET NOT NULL');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_A02FA639E7927C74 ON hulpverlener (email)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_A02FA639E7927C74');
        $this->addSql('ALTER TABLE hulpverlener DROP password');
        $this->addSql('ALTER TABLE hulpverlener DROP is_active');
    }
}
