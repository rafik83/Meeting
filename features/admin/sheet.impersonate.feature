@admin @sheet

Feature: Impersonation
  As an admin, I can connect to a sheet owner on front

  Scenario: I can impersonate to a user sheet on my event
    Given the database is purged
    And the event "Forum PHP" is created
    And there is a sheet with the title "Proximum"
    And I am logged as admin
    And I am on this page "/fr/event/1/sheet"
    And I am on this page "/fr/event/1/sheet/1"
    When I press "admin.sheet.impersonate"
    Then I should be on this url "http://forum-php.vimeet.proximum/fr/sheet/1"
    And I should see "admin.sheet.exit_impersonation"
    And I should see "sheet.title"
    When I follow "admin.sheet.exit_impersonation"
    Then I should be on this page "/fr/event/1/sheet/1"
