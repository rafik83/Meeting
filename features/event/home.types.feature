@event
@type
Feature: Show the homepage of an event
  I need to be able to see the event name and description

  Scenario: I can see the event name
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
    When I go to this page "http://rdv-carnot-2016.vimeet.proximum/app_test.php/fr/"
    Then I should see "Les rendez-vous CARNOT 2016"

  Scenario: Show the participant types of 'Les rendez-vous Carnot 2016' in French
    When I go to this page "http://rdv-carnot-2016.vimeet.proximum/app_test.php/fr/"
    Then I should see "LES RENDEZ-VOUS DE LA R&D POUR LES ENTREPRISES"
    And I should see "Exposant"
    And I should see "Visiteur"

  Scenario: Show the participant types of 'Les rendez-vous Carnot 2016' in English
    When I go to this page "http://rdv-carnot-2016.vimeet.proximum/app_test.php/en/"
    Then I should see "Les rendez-vous CARNOT 2016"
    And I should see "In 7 editions, les Rendez-vous CARNOT became the major R&D event for innovation."
    And I should see "Exhibitor"
    And I should see "Visitor"

  Scenario: See pratical info of the event
    When I go to this page "http://rdv-carnot-2016.vimeet.proximum/app_test.php/fr/"
    Then I should see "event.info.contact"
    And I should see "PROXIMUM"
    And I should see "accounts@proximumgroup.com"
    And I should see "event.info.close"
