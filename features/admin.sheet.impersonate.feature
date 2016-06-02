Feature: Impersonation
  As an admin, I can connect to a sheet owner on front

  Scenario: I can impersonate to a user sheet on my event
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
      | Admin.yml                                                                |
    And elastica is populate
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    And I follow "admin.sheet.link"
    And I should be on this page "/admin/fr/event/1/sheet"
    When I follow "admin.sheet.impersonate"
    Then I should be on this url "http://asddays-2016.vimeet.proximum.dev/app_test.php/fr/sheet"
    And I should see "admin.sheet.exit_impersonation"
    And I should see "FICHE DE PRÉSENTATION"
    When I follow "admin.sheet.exit_impersonation"
    Then I should be on this page "/admin/fr/event/1/sheet"
