@event
@sheet
Feature: Sheet with a tag in editable text title
    I see the organization name in the sheet title

    Scenario: I can see the organization name in the sheet title
        Given the database is purged
        And the following fixtures files are loaded:
            | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
            | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
            | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
            | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
            | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
            | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
            | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
            | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
            | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
        And I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
        When I go to this page "/fr"
        Then I should be on this page "/fr/sheet/2"
        And I should see "Titre de votre fiche"
        And I should not see "Aanera"

    Scenario: I can see the organization name in the sheet title
        Given I am logged with "user_asddays_3@proximum.com" on event "http://asddays-2016.vimeet.proximum"
        When I go to this page "/fr"
        Then I should be on this page "/fr/sheet/3"
        And I should see "World Company Inc"
