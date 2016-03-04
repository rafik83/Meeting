Feature: See meeting request
  I can see meeting request

  Background: Re-init the database and load the fixtures
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
    Given I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event"

  Scenario: list meeting request
    When I follow "Demandes de RDV"
    Then the response status code should be 200
    And I should be on "/admin/fr/event/1/meeting-request"
    And I should see "Elao"

