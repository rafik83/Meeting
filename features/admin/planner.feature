@admin @planner
Feature: See planner page
  As an admin, I can access the page of the planner

  Scenario: I can access the page of the planner
    Given the database is purged
    And the event "Best of Planner" is created
    And the super admin "test@test.com" is created
    And I am logged with this admin
    And I am on the homepage of the admin
    When I follow "admin.planner.index"
    Then the response status code should be 200
    And I should be on this page "/fr/event/1/planner"
