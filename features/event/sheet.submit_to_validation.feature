@event @sheet @mail
Feature: Sheet validation workflow
  I can send my sheet to validation when I think I'm done

  Scenario: I can send my sheet to validation
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
      | @InfrastructureBundle/DataFixtures/ORM/AdminWithType.yml                 |
    And I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    And I should see "sheet.submit.validation"
    When I follow "sheet.submit.validation"
    Then I should be on this page "/fr/sheet/1"
    And I should see "sheet.submit.validation.pending"
