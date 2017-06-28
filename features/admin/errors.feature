@admin
@errors
Feature: In admin, I should see 404 for an unknown route

  Scenario: I should see 404 for an unknown route
    Given the database is purged
    And the event "Proximum Event" is created
    And the user "vincent@proximum.com" is created
    When I am logged with this user
    Then this page "/fr/unknow-route" returns 404
    And I should see "error.404.content"
