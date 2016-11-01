@admin

Feature: Edit event billing configuration
  As an Admin, I need to be able to edit event billing configuration

  Scenario: go to edit billing configuration page
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    Then I am on this page "/admin/fr/event"
    And I go to this page "/admin/fr/event/1/billing/configuration"
    Then the response status code should be 200
    And I should see "event.billing.configuration.title"
