<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170404073141 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     *
     * @throws SkipMigrationException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema)
    {
        if ($schema->hasTable($this->prefix.'webhook_trigger_queue')) {
            throw new SkipMigrationException('Schema includes this migration');
        }
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE '.$this->prefix.'webhook_trigger_queue (id INT AUTO_INCREMENT NOT NULL, trigger_id INT NOT NULL, event_id INT NOT NULL, date_added DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', payload LONGTEXT NOT NULL, INDEX '.$this->generatePropertyName('webhook_trigger_queue', 'idx', ['trigger_id']).' (trigger_id), INDEX '.$this->generatePropertyName('webhook_trigger_queue', 'idx', ['event_id']).' (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE '.$this->prefix.'webhook_trigger_queue ADD CONSTRAINT '.$this->generatePropertyName('webhook_trigger_queue', 'fk', ['trigger_id']).' FOREIGN KEY (trigger_id) REFERENCES '.$this->prefix.'point_triggers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE '.$this->prefix.'webhook_trigger_queue ADD CONSTRAINT '.$this->generatePropertyName('webhook_trigger_queue', 'fk', ['event_id']).' FOREIGN KEY (event_id) REFERENCES '.$this->prefix.'point_trigger_events (id) ON DELETE CASCADE');
    }
}
