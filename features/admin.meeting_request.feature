Feature: See meeting request
  I can see meeting requests

  Scenario: list meeting request
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                       |
      | app/Event.yml                          |
      | app/Type.yml                           |
      | app/Category.yml                       |
      | TwoSheetSeveralParticipantWithData.yml |
      | User.yml                               |
      | MeetingRequest.yml                     |
      | Admin.yml                              |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    When I follow "admin.meeting_request.link"
    Then the response status code should be 200
    And I should be on this page "/admin/fr/event/1/meeting-request"
    And I should see "Elao"

