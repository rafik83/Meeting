@event @catalog @external
Feature: External catalog
  Scenario: I can visit external catalog
    Given the database is purged
    And the event "Proximum Event" is created
    And the external catalog is open
    And the catalog visibility is configured
    And the user "vincent@proximum.com" is created
    And elastica is populate
    And I am logged with this user
    When I go to "/fr/list"
    Then the response status code should be 200
