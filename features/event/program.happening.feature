@event @happening @program
Feature: Program Happening
  As a participant, I can see the program of happening of the event

  Scenario: I can see the program of happening of the event
    Given the database is empty
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
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Rule.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Happening.yml         |
    And elastica is populate
    When I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum.dev"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet"
    When I go to this page "/fr/program"
    Then I should be on this page "/fr/program"
    And I should see "Elao Talk"
    And I should see "Conférence 3"
    And I should see "Proximum conf"
