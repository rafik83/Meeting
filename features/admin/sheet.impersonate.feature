@admin @sheet

Feature: Impersonation
  As an admin, I can connect to a sheet owner on front

  Scenario: I can impersonate to a user sheet on my event
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
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
      | Admin.yml                                                                |
    And elastica is populate
    And I am logged with "test@test.com" on admin
    And I am on this page "/fr/event/1/sheet"
    And I am on this page "/fr/event/1/sheet/1"
    When I follow "admin.sheet.impersonate"
    Then I should be on this url "http://asddays-2016.vimeet.proximum/fr/sheet/1"
    And I should see "admin.sheet.exit_impersonation"
    And I should see "sheet.title"
    When I follow "admin.sheet.exit_impersonation"
    Then I should be on this page "/fr/event/1/sheet/1"
