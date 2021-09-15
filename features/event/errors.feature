@admin
@errors
Feature: As a participant, I should see 404 for an unknown route

  Scenario: I should see 404 for an unknown route
    Given the database is purged
    When the event "Proximum Event" is created
    And I am on the homepage of this event
    Then this page "/fr/unknown-route" returns 404
    And I should see "error.404.content"
