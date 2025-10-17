<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251017152819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livre ADD auteur_id INT NOT NULL, ADD categorie_id INT NOT NULL, CHANGE disponible disponible TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE livre ADD CONSTRAINT FK_AC634F9960BB6FE6 FOREIGN KEY (auteur_id) REFERENCES auteur (id)');
        $this->addSql('ALTER TABLE livre ADD CONSTRAINT FK_AC634F99BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('CREATE INDEX IDX_AC634F9960BB6FE6 ON livre (auteur_id)');
        $this->addSql('CREATE INDEX IDX_AC634F99BCF5E72D ON livre (categorie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livre DROP FOREIGN KEY FK_AC634F9960BB6FE6');
        $this->addSql('ALTER TABLE livre DROP FOREIGN KEY FK_AC634F99BCF5E72D');
        $this->addSql('DROP INDEX IDX_AC634F9960BB6FE6 ON livre');
        $this->addSql('DROP INDEX IDX_AC634F99BCF5E72D ON livre');
        $this->addSql('ALTER TABLE livre DROP auteur_id, DROP categorie_id, CHANGE disponible disponible TINYINT(1) DEFAULT NULL');
    }
}
