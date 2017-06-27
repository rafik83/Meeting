@admin
Feature: As a participant, I should see errors message in case of wrong route

  Scenario: I should see errors message in case of wrong route
    Given the database is purged
    And the following fixtures files are loaded:
      | Admin.yml                                                                   |
    When I am logged with "test@test.com" on admin
    Then this page "/fr/unknow-route" returns 404
    And I should see "error.404.content"
