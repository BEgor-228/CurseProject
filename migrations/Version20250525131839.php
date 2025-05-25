<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525131839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE Administrator (Administrator_ID SERIAL NOT NULL, Administrator_password VARCHAR(10) NOT NULL, Administrator_mail VARCHAR(40) NOT NULL, Administrator_fullname VARCHAR(40) NOT NULL, PRIMARY KEY(Administrator_ID))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE Hall (Hall_ID SERIAL NOT NULL, Hall_size VARCHAR(15) DEFAULT NULL, Hall_capacity INT NOT NULL, PRIMARY KEY(Hall_ID))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE List (List_ID SERIAL NOT NULL, Title VARCHAR(30) NOT NULL, Performance_ID INT NOT NULL, Repertoire_ID INT NOT NULL, PRIMARY KEY(List_ID))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E4FA5726DA94A25C ON List (Performance_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E4FA57263F5F816 ON List (Repertoire_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE Manager (Manager_ID SERIAL NOT NULL, Manager_password VARCHAR(10) NOT NULL, Manager_mail VARCHAR(40) NOT NULL, Manager_fullname VARCHAR(40) NOT NULL, Administrator_ID INT NOT NULL, PRIMARY KEY(Manager_ID))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_35991C25F2DC963B ON Manager (Administrator_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE Performance (Performance_ID SERIAL NOT NULL, Performance_description VARCHAR(300) NOT NULL, Performance_castList VARCHAR(300) NOT NULL, Performance_duration TIME(0) WITHOUT TIME ZONE NOT NULL, Performance_data DATE NOT NULL, Performance_title VARCHAR(30) NOT NULL, Performance_price DOUBLE PRECISION NOT NULL, Performance_genre VARCHAR(15) NOT NULL, Hall_ID INT NOT NULL, Manager_ID INT NOT NULL, PRIMARY KEY(Performance_ID))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_44B195633F8F220 ON Performance (Hall_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_44B1956998933D9 ON Performance (Manager_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE Place (Place_ID SERIAL NOT NULL, Place_number INT NOT NULL, Place_level INT NOT NULL, Place_status VARCHAR(15) NOT NULL, Hall_ID INT NOT NULL, PRIMARY KEY(Place_ID))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B5DC7CC933F8F220 ON Place (Hall_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE Repertoire (Repertoire_ID SERIAL NOT NULL, Repertoire_title VARCHAR(30) NOT NULL, Repertoire_size INT NOT NULL, Manager_ID INT NOT NULL, PRIMARY KEY(Repertoire_ID))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_736B7BA6998933D9 ON Repertoire (Manager_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE Ticket (Ticket_ID SERIAL NOT NULL, Ticket_purchaseDate DATE NOT NULL, Ticket_hallNumber INT NOT NULL, Viewer_ID INT NOT NULL, Hall_ID INT NOT NULL, Place_ID INT NOT NULL, Performance_ID INT NOT NULL, PRIMARY KEY(Ticket_ID))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_900CA8954042419B ON Ticket (Viewer_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_900CA89533F8F220 ON Ticket (Hall_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_900CA8955A3AC425 ON Ticket (Place_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_900CA895DA94A25C ON Ticket (Performance_ID)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE Viewer (Viewer_ID SERIAL NOT NULL, Viewer_password VARCHAR(10) NOT NULL, Viewer_mail VARCHAR(40) NOT NULL, Viewer_fullname VARCHAR(40) NOT NULL, PRIMARY KEY(Viewer_ID))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE List ADD CONSTRAINT FK_E4FA5726DA94A25C FOREIGN KEY (Performance_ID) REFERENCES Performance (Performance_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE List ADD CONSTRAINT FK_E4FA57263F5F816 FOREIGN KEY (Repertoire_ID) REFERENCES Repertoire (Repertoire_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Manager ADD CONSTRAINT FK_35991C25F2DC963B FOREIGN KEY (Administrator_ID) REFERENCES Administrator (Administrator_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Performance ADD CONSTRAINT FK_44B195633F8F220 FOREIGN KEY (Hall_ID) REFERENCES Hall (Hall_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Performance ADD CONSTRAINT FK_44B1956998933D9 FOREIGN KEY (Manager_ID) REFERENCES Manager (Manager_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Place ADD CONSTRAINT FK_B5DC7CC933F8F220 FOREIGN KEY (Hall_ID) REFERENCES Hall (Hall_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Repertoire ADD CONSTRAINT FK_736B7BA6998933D9 FOREIGN KEY (Manager_ID) REFERENCES Manager (Manager_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Ticket ADD CONSTRAINT FK_900CA8954042419B FOREIGN KEY (Viewer_ID) REFERENCES Viewer (Viewer_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Ticket ADD CONSTRAINT FK_900CA89533F8F220 FOREIGN KEY (Hall_ID) REFERENCES Hall (Hall_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Ticket ADD CONSTRAINT FK_900CA8955A3AC425 FOREIGN KEY (Place_ID) REFERENCES Place (Place_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Ticket ADD CONSTRAINT FK_900CA895DA94A25C FOREIGN KEY (Performance_ID) REFERENCES Performance (Performance_ID) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE List DROP CONSTRAINT FK_E4FA5726DA94A25C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE List DROP CONSTRAINT FK_E4FA57263F5F816
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Manager DROP CONSTRAINT FK_35991C25F2DC963B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Performance DROP CONSTRAINT FK_44B195633F8F220
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Performance DROP CONSTRAINT FK_44B1956998933D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Place DROP CONSTRAINT FK_B5DC7CC933F8F220
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Repertoire DROP CONSTRAINT FK_736B7BA6998933D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Ticket DROP CONSTRAINT FK_900CA8954042419B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Ticket DROP CONSTRAINT FK_900CA89533F8F220
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Ticket DROP CONSTRAINT FK_900CA8955A3AC425
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Ticket DROP CONSTRAINT FK_900CA895DA94A25C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Administrator
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Hall
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE List
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Manager
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Performance
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Place
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Repertoire
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Ticket
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Viewer
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
