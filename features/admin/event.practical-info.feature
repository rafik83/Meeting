@admin
@admin-event
Feature: See and update practical information
  I need to be able to see and init practical information for an event

  Scenario: Set practical information
    Given the database is purged
    And the event "La communication c'est la clé du succès" is created
    And I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.practicalInfo.link"
    Then the response status code should be 200
    And I fill in the following:
      | event_practical_info_update_organiserName    | proximum           |
      | event_practical_info_update_organiserEmail   | ceo@proximum.com   |
      | event_practical_info_update_organiserPhone   | 0102030405         |
      | event_practical_info_update_organiserWebsite | proximum-group.com |
      | event_practical_info_update_contactFirstName | jean               |
      | event_practical_info_update_contactLastName  | dupont             |
    And I press "event_practical_info_update_submit"

  Scenario: See practical information
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.practicalInfo.link"
    Then the response status code should be 200
    And the "event_practical_info_update_organiserName" field should contain "proximum"
    And the "event_practical_info_update_organiserEmail" field should contain "ceo@proximum.com"
    And the "event_practical_info_update_organiserPhone" field should contain "0102030405"
    And the "event_practical_info_update_organiserWebsite" field should contain "http://proximum-group.com"
    And the "event_practical_info_update_contactFirstName" field should contain "jean"
    And the "event_practical_info_update_contactLastName" field should contain "dupont"
