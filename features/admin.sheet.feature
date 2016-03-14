Feature: List sheet
  I can see sheets

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml                       |
      | app/Event.yml                          |
      | app/Type.yml                           |
      | app/Category.yml                       |
      | User.yml                               |
      | TwoSheetSeveralParticipantWithData.yml |
      | Admin.yml                              |
    Given I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event"

  Scenario: I can list sheet of an event
    When I follow "admin.sheet.link"
    Then the response status code should be 200
    And I should be on "/admin/fr/event/1/sheet"
    And I should see "Elao"
    And I should see "Oale"
