Feature: Meeting Request / Proposition
  I need to be able to see the meeting request and proposition

  Scenario: I can see my meeting request
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
    And I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    When I follow "event.link.see_meeting_request"
    Then the response status code should be 200
    And I should be on "/fr/sheet/1/meeting/request"

  Scenario: I can see my meeting proposition
    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/"
    When I follow "event.link.see_meeting_proposition"
    Then the response status code should be 200
    And I should be on "/fr/sheet/1/meeting/proposition"

  #
  # Rewrite the catalog in order to request meetings
  #

#  Scenario: I can request someone for a rendez-vous
#    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
#    And I go to this page "/fr/"
#    When I follow "event.link.see_catalog"
#    Then the response status code should be 200
#    And I follow "Exposant"
#    Then the response status code should be 200
#    And I should be on "/fr/catalog/1"
#    And I should see "event.catalog.category.seeSheet"
#    Then I go to "/fr/catalog/1/sheet/2"
#    And I should see "event.catalog.sheet.meetingRequest"
#    Then I follow "event.catalog.sheet.meetingRequest"
#    And the response status code should be 200
#    And I should be on "/fr/catalog/1/sheet/2/meeting/request/from/1"
#    And I check "Jean Dutest"
#    And I fill in the following:
#      | form.meeting_request_create.children.description.label | This is a test |
#    And I press "form.meeting_request_create.children.submit.label"
#    And the response status code should be 200
#    And I should see "flash.meeting_request.create.success"
#    Then I go to "/fr/sheet/1/meeting/request"
#    And the response status code should be 200
#    And I should see "event.meeting.request.state.from.sent"
#    And I should see "Oale"
#    And I should see "Jean Dutest"
#
#  Scenario: I can accept a rendez-vous
#    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
#    When I follow "event.link.see_catalog"
#    Then the response status code should be 200
#    And I follow "Exposant"
#    Then the response status code should be 200
#    And I should be on "/fr/catalog/1"
#    And I should see "event.catalog.category.seeSheet"
#    Then I go to "/fr/catalog/1/sheet/2"
#    And I should see "event.catalog.sheet.meetingRequest"
#    Then I follow "event.catalog.sheet.meetingRequest"
#    And the response status code should be 200
#    And I should be on "/fr/catalog/1/sheet/2/meeting/request/from/1"
#    And I check "Jean Dutest"
#    And I fill in the following:
#      | form.meeting_request_create.children.description.label | This is a test |
#    And I press "form.meeting_request_create.children.submit.label"
#    And the response status code should be 200
#    And I should see "flash.meeting_request.create.success"
#    Then I go to "/fr/sheet/1/meeting/request"
#    And the response status code should be 200
#    And I should see "event.meeting.request.state.from.sent"
#    And I should see "Oale"
#    And I should see "Jean Dutest"
#    Then I go to "/fr/logout"
#    Given I am logged with "test-3@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
#    And I go to this page "/fr/"
#    Then I follow "event.link.see_meeting_proposition"
#    And the response status code should be 200
#    And I should see "Elao"
#    Then I follow "event.meeting.listProposition.accept"
#    And the response status code should be 200
#    And I should be on "/fr/sheet/2/meeting/request/1/approve"
#    And I check "Paul Truc"
#    Then I press "form.meeting_request_approve.children.submit.label"
#    And I should be on "/fr/sheet/2/meeting/proposition"
#    And I should see "flash.meeting_request.approved.success"
#    And I should see "event.meeting.request.state.to.approved"
#    Then I go to "/fr/logout"
#    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
#    Then I go to "/fr/sheet/1/meeting/request"
#    And I should see "event.meeting.request.state.from.approved"
#    Then I follow "event.meeting.listRequest.cancel"
#    Then I should be on "/fr/sheet/1/meeting/request/1/cancel"
#    And the response status code should be 200
#    And I fill in the following:
#      | form.meeting_request_cancel.children.message.label | Sorry I can't |
#    Then I press "form.meeting_request_cancel.children.submit.label"
#    Then I should be on "/fr/sheet/1/meeting/request"
#    And the response status code should be 200
#    And I should see "flash.meeting_request.cancelled.success"
#    And I should see "event.meeting.request.state.from.cancelled"
#
#  Scenario: I can refuse a rendez-vous
#    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
#    And I go to this page "/fr/"
#    And I follow "event.link.see_catalog"
#    Then the response status code should be 200
#    And I follow "Exposant"
#    Then the response status code should be 200
#    And I should be on "/fr/catalog/1"
#    And I should see "event.catalog.category.seeSheet"
#    Then I go to "/fr/catalog/1/sheet/2"
#    And I should see "event.catalog.sheet.meetingRequest"
#    Then I follow "event.catalog.sheet.meetingRequest"
#    And the response status code should be 200
#    And I should be on "/fr/catalog/1/sheet/2/meeting/request/from/1"
#    And I check "Jean Dutest"
#    And I fill in the following:
#      | form.meeting_request_create.children.description.label | This is a test |
#    And I press "form.meeting_request_create.children.submit.label"
#    And the response status code should be 200
#    And I should see "flash.meeting_request.create.success"
#    Then I go to "/fr/sheet/1/meeting/request"
#    And the response status code should be 200
#    And I should see "event.meeting.request.state.from.sent"
#    And I should see "Oale"
#    And I should see "Jean Dutest"
#    Then I go to "/fr/logout"
#    Given I am logged with "test-3@test.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
#    And I go to this page "/fr/"
#    Then I follow "event.link.see_meeting_proposition"
#    And the response status code should be 200
#    And I should see "Elao"
#    Then I follow "event.meeting.listProposition.refuse"
#    And the response status code should be 200
#    And I should be on "/fr/sheet/2/meeting/request/1/refuse"
#    And I fill in the following:
#      | form.meeting_request_refuse.children.message.label | Sorry I can't |
#    Then I press "form.meeting_request_refuse.children.submit.label"
#    And I should be on "/fr/sheet/2/meeting/proposition"
#    And I should see "flash.meeting_request.refused.success"
#    And I should see "event.meeting.request.state.to.refused"
#    Then I go to "/fr/logout"
#    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
#    Then I go to "/fr/sheet/1/meeting/request"
#    And I should see "event.meeting.request.state.from.refused"
#    And I should see "Sorry I can't"
#    And I should not see "event.meeting.request.state.from.cancelled"
