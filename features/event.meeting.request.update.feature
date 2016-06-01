Feature: Meeting Request Update
  I need to be able update my meeting request and proposition when they are sent or approved

  Scenario: I can update my meeting request
    Given the database is empty
    Given the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/RdvCarnot2016-Request.yml |
    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    And I follow "event.link.see_meeting_request"
    Then the response status code should be 200
    And I should be on this page "/fr/sheet/1/meeting/request"
    And I should see "event.meeting.request.state.from.sent"
    And I follow "event.meeting.listRequest.edit"
    Then the response status code should be 200
    And I should be on this page "/fr/sheet/1/meeting/request/1/edit"
    And I check "Paul GASCOIGNE"
    And I fill in "form.meeting_request_update_from.children.description.label" with "My meeting request description"
    And I press "form.meeting_request_update_from.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.meeting_request.edit.success"

  Scenario: I can list my meeting request
    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    When I follow "event.link.see_meeting_request"
    Then the response status code should be 200
    And I should be on this page "/fr/sheet/1/meeting/request"
    And I should see "event.meeting.request.state.from.approved"

  Scenario: I can list my meeting proposition
    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    When I follow "event.link.see_meeting_proposition"
    Then the response status code should be 200
    And I should be on this page "/fr/sheet/1/meeting/proposition"
    And I should see "event.meeting.request.state.to.approved"
