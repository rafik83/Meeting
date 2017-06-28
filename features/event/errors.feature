@admin
@errors
Feature: As a participant, I should see 404 for an unknown route

  Scenario: I should see 404 for an unknown route
    Given the database is purged
    And the following fixtures files are loaded:
      | Admin.yml        |
    When I am logged with "test@test.com" on admin
    Then this page "/fr/unknow-route" returns 404
    And I should see "error.404.content"
