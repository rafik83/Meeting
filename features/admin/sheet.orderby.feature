@admin @sheet

Feature: Sheet participations list order by
  As an admin, I can order the sheet participations list

  Scenario: I can order by alphabetical
    Given the database is purged
    And the event "Foire de Paris" is created
    And there is a sheet with the title "Anera"
    And there is a sheet with the title "Zoro"
    And elastica is populate
    And the super admin "test@test.fr" is created
    When I am logged with "test@test.fr" on admin
    Then I go to this page "/fr/event/1/sheet?orderBy=alphabetical"
    And the index "1" of the table should contain "Anera"
    And the index "2" of the table should contain "Zoro"

  Scenario: I can order by completeness
    Given the database is purged
    And the event "Foire de Paris" is created
    And there is a sheet with the title "Anera"
    And this sheet has "40" as completeness
    And there is a sheet with the title "Zoro"
    And this sheet has "80" as completeness
    And elastica is populate
    And the super admin "test@test.fr" is created
    When I am logged with "test@test.fr" on admin
    Then I go to this page "/fr/event/1/sheet?orderBy=completeness"
    And the index "1" of the table should contain "Zoro"
    And the index "2" of the table should contain "Anera"

  Scenario: I can order by created at
    Given the database is purged
    And the event "Foire de Paris" is created
    And there is a sheet with the title "Anera" registered at "2018-10-10 10:00:00.000"
    And there is a sheet with the title "Zoro" registered at "2018-10-07 10:00:00.000"
    And elastica is populate
    And the super admin "test@test.fr" is created
    When I am logged with "test@test.fr" on admin
    Then I go to this page "/fr/event/1/sheet?orderBy=created_at"
    And the index "1" of the table should contain "Anera"
    And the index "2" of the table should contain "Zoro"
