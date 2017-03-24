<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170322083012 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE campaign_event_daily_send_log (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, sent_count INT NOT NULL, date DATE DEFAULT NULL, INDEX IDX_1DE0BCA71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE campaign_event_daily_send_log ADD CONSTRAINT FK_1DE0BCA71F7E88B FOREIGN KEY (event_id) REFERENCES campaign_events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE campaign_lead_event_log ADD is_queued TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE dynamic_content_lead_data DROP FOREIGN KEY FK_515B221BD9D0CD7');
        $this->addSql('ALTER TABLE dynamic_content_lead_data ADD CONSTRAINT FK_515B221BD9D0CD7 FOREIGN KEY (dynamic_content_id) REFERENCES dynamic_content (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE campaign_event_daily_send_log');
        $this->addSql('ALTER TABLE campaign_lead_event_log DROP is_queued');
        $this->addSql('ALTER TABLE dynamic_content_lead_data DROP FOREIGN KEY FK_515B221BD9D0CD7');
        $this->addSql('ALTER TABLE dynamic_content_lead_data ADD CONSTRAINT FK_515B221BD9D0CD7 FOREIGN KEY (dynamic_content_id) REFERENCES dynamic_content (id) ON DELETE CASCADE');
    }
}
