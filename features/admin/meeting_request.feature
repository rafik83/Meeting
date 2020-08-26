@admin
@meeting
Feature: See meeting request
  I can see meeting requests

  Background:
    Given the database is purged
    And the event "RdvCarnot" is created
    And I am logged as admin
    And there is an active spot "Spot01" with size of 1, meeting capacity of 1, seat capacity of 2
    And there is 1 slot in this event
    And there is a request between "Proximum" and "Acme"
    And there is a meeting for this request on slot "1" and spot "Spot01"
    And I am on this page "/fr/event"

  Scenario: list meeting request
    When I follow "admin.meeting_request.link"
    Then I should be on this page "/fr/event/1/meeting-request"
    And I should see "Acme"

  Scenario: I can filter meeting request list by meeting request planned
    When I follow "admin.meeting_request.link"
    And I should be on this page "/fr/event/1/meeting-request"
    And I should see "Proximum"
    And I should see "Acme"
    When I follow "admin.meeting_request.state.planned"
    Then I should see "Proximum"
    And I should see "Acme"
    When I follow "admin.meeting_request.state.approved"
    Then I should not see "Proximum"
    And I should not see "Acme"
