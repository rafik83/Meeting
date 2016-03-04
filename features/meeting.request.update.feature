Feature: Meeting Request Update
  I need to be able update my meeting request and proposition when they are sent or approved

  Scenario: I can update my meeting request that is not yet accepted
    Given the database is empty
    Given the following fixtures files are loaded:
      | app/Template.yml                       |
      | app/Event.yml                          |
      | app/Type.yml                           |
      | app/Category.yml                       |
      | app/Rule.yml                           |
      | User.yml                               |
      | TwoSheetSeveralParticipantWithData.yml |
      | MeetingRequestStateSent.yml            |
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    And I follow "event.link.see_meeting_request"
    Then the response status code should be 200
    And I should be on "/fr/sheet/1/meeting/request"
    And I should see "event.meeting.request.state.from.sent"
    And I follow "event.meeting.listRequest.edit"
    Then the response status code should be 200
    And I should be on "/fr/sheet/1/meeting/request/1/edit"
    And I check "Jean Dutest"
    And I fill in "form.meeting_request_update_from.children.description.label" with "ff"
    And I press "form.meeting_request_update_from.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.meeting_request.edit.success"

  Scenario: I can update my meeting request that is accepted
    Given the database is empty
    Given the following fixtures files are loaded:
      | app/Template.yml                       |
      | app/Event.yml                          |
      | app/Type.yml                           |
      | app/Category.yml                       |
      | app/Rule.yml                           |
      | User.yml                               |
      | TwoSheetSeveralParticipantWithData.yml |
      | MeetingRequestStateApproved.yml        |
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    And I follow "event.link.see_meeting_request"
    Then the response status code should be 200
    And I should be on "/fr/sheet/1/meeting/request"
    And I should see "event.meeting.request.state.from.approved"
    And I follow "event.meeting.listRequest.edit"
    Then the response status code should be 200
    And I should be on "/fr/sheet/1/meeting/request/1/edit"
    And I check "Jean Dutest"
    And I fill in "form.meeting_request_update_from.children.description.label" with "ff"
    And I press "form.meeting_request_update_from.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.meeting_request.edit.success"

  Scenario: I can update my meeting proposition that is accepted
    Given the database is empty
    Given the following fixtures files are loaded:
      | app/Template.yml                       |
      | app/Event.yml                          |
      | app/Type.yml                           |
      | app/Category.yml                       |
      | app/Rule.yml                           |
      | User.yml                               |
      | TwoSheetSeveralParticipantWithData.yml |
      | MeetingRequest.yml                     |
    Given I am logged with "test@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    And I follow "event.link.see_meeting_proposition"
    Then the response status code should be 200
    And I should be on "/fr/sheet/1/meeting/proposition"
    And I should see "event.meeting.request.state.to.approved"
    And I follow "event.meeting.listProposition.edit"
    Then the response status code should be 200
    And I should be on "/fr/sheet/1/meeting/request/3/edit"
    And I check "Jean Dutest"
    And I fill in "form.meeting_request_update_to.children.description.label" with "ff"
    And I press "form.meeting_request_update_to.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.meeting_request.edit.success"
