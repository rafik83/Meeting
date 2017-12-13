@event @navigation
Feature: Navigate in the application using menu

  Scenario: I can change my application language and stay where I am
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
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    When I follow "en"
    Then I should be on this page "/en/sheet/1"
    When I follow "navigation.links.member_space.profile"
    Then I should be on this page "/en/account/sheet/1/participant/1"
    When I follow "fr"
    Then I should be on this page "/fr/account/sheet/1/participant/1"

