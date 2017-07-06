@admin
@errors
Feature: In admin, I should see 404 for an unknown route

  Scenario: I should see 404 for an unknown route
    Given the database is purged
    When I am on the homepage of the admin
    Then this page "/fr/unknow-route" returns 404
    And I should see "error.404.content"
