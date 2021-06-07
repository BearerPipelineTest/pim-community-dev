<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_6_0_20210527123656 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE pim_api_auth_code ADD client_id INT DEFAULT NULL, ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pim_api_auth_code ADD CONSTRAINT FK_AD5DC7C619EB6921 FOREIGN KEY (client_id) REFERENCES pim_api_client (id)');
        $this->addSql('ALTER TABLE pim_api_auth_code ADD CONSTRAINT FK_AD5DC7C6A76ED395 FOREIGN KEY (user_id) REFERENCES oro_user (id)');
        $this->addSql('CREATE INDEX IDX_AD5DC7C619EB6921 ON pim_api_auth_code (client_id)');
        $this->addSql('CREATE INDEX IDX_AD5DC7C6A76ED395 ON pim_api_auth_code (user_id)');
     }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}