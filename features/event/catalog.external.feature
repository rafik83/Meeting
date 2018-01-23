@event @catalog @external
Feature: External catalog
  Scenario: I can visit external catalog
    Given the database is purged
    And the event "Proximum Event" is created
    And the external catalog is open
    And the catalog visibility is configured
    And elastica is populate
    And I am on the homepage of this event
    When I go to this page "/fr/list"
    Then I should be on this page "/fr/list"

  Scenario: The register link from external catalog redirect to another page
    Given the database is purged
    And the event "Proximum Event" is created
    And the external catalog is open
    And the catalog visibility is configured
    And the catalog visibility registration url is "http://super-event.vimeet.proximum/fr/login"
    And there is a sheet with the title "Elao"
    And this sheet is validated
    And this sheet is enabled
    And there is a sheet with the title "Proximum"
    And this sheet is validated
    And this sheet is enabled
    And Allow all types to be visible on catalog visibility
    And elastica is populate
    And I am on the homepage of this event
    When I go to this page "/fr/list"
    Then I should be on this page "/fr/list"
    When I follow "register.register"
    Then I should be on this page "/fr/login"
    When I go to this page "/fr/list"
    Then I should be on this page "/fr/list"
    When I follow "modal-register-link"
    Then I should be on this page "/fr/login"
