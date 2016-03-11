Feature: Impersonation
  As an admin, I can connect to a sheet owner on front

  Scenario: I can impersonate to a user sheet on my event
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                |
      | app/Event.yml                   |
      | app/Type.yml                    |
      | app/Category.yml                |
      | User.yml                        |
      | Sheet.yml                       |
      | OneSheetSeveralParticipants.yml |
      | Admin.yml                       |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/event"
    And I follow "admin.sheet.link"
    And I should be on this page "/admin/event/1/sheet"
    When I follow "admin.sheet.impersonate"
    Then I should be on this url "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1"
    And I should see "admin.sheet.exit_impersonation"
    And I should see "Exposant"
    And I should see "Dutest"
    When I follow "admin.sheet.exit_impersonation"
    Then I should be on this page "/admin/event/1/sheet"
