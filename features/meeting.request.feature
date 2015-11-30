Feature: Meeting Request / Proposition
  I need to be able to see the meeting request and proposition

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Template.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Event.yml    |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Type.yml     |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Category.yml |
      | @VimeetInfrastructureBundle/DataFixtures/ORM/Rule.yml     |
      | User.yml                                                  |
      | TwoSheetSeveralParticipantWithData.yml                    |

  Scenario: I can see my meeting request
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then the response status code should be 200
    And I follow "event.link.see_meeting_request"
    Then the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/meeting/request"

  Scenario: I can see my meeting proposition
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then the response status code should be 200
    And I follow "event.link.see_meeting_proposition"
    Then the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/meeting/proposition"

  Scenario: I can request someone for a rendez-vous
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then the response status code should be 200
    And I follow "event.link.see_catalog"
    Then the response status code should be 200
    And I follow "Exposant"
    Then the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/catalog/1"
    And I should see "event.catalog.category.seeSheet"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/catalog/1/sheet/2"
    And I should see "event.catalog.sheet.meetingRequest"
    Then I follow "event.catalog.sheet.meetingRequest"
    And the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/catalog/1/sheet/2/meeting/request/from/1"
    And I check "Dutest Jean"
    And I fill in the following:
    | form.meeting_request_create.children.description.label | This is a test |
    And I press "form.meeting_request_create.children.submit.label"
    And the response status code should be 200
    And I should see "flash.meeting_request.create.success"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/meeting/request"
    And the response status code should be 200
    And I should see "event.meeting.request.state.from.sent"
    And I should see "Oale"
    And I should see "Dutest Jean"

  Scenario: I can accept a rendez-vous
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then the response status code should be 200
    And I follow "event.link.see_catalog"
    Then the response status code should be 200
    And I follow "Exposant"
    Then the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/catalog/1"
    And I should see "event.catalog.category.seeSheet"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/catalog/1/sheet/2"
    And I should see "event.catalog.sheet.meetingRequest"
    Then I follow "event.catalog.sheet.meetingRequest"
    And the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/catalog/1/sheet/2/meeting/request/from/1"
    And I check "Dutest Jean"
    And I fill in the following:
      | form.meeting_request_create.children.description.label | This is a test |
    And I press "form.meeting_request_create.children.submit.label"
    And the response status code should be 200
    And I should see "flash.meeting_request.create.success"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/meeting/request"
    And the response status code should be 200
    And I should see "event.meeting.request.state.from.sent"
    And I should see "Oale"
    And I should see "Dutest Jean"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/logout"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And the response status code should be 200
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "form.login.children.username.label" with "test-3@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I follow "event.link.see_meeting_proposition"
    And the response status code should be 200
    And I should see "Elao"
    Then I follow "event.meeting.listProposition.accept"
    And the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/2/meeting/proposition/approved/1"
    And I check "Truc Paul"
    Then I press "form.meeting_request_approve.children.submit.label"
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/2/meeting/proposition"
    And I should see "flash.meeting_request.approved.success"
    And I should see "event.meeting.request.state.to.approved"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/logout"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And the response status code should be 200
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then the response status code should be 200
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/meeting/request"
    And I should see "event.meeting.request.state.from.approved"

  Scenario: I can refuse a rendez-vous
    When I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then the response status code should be 200
    And I follow "event.link.see_catalog"
    Then the response status code should be 200
    And I follow "Exposant"
    Then the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/catalog/1"
    And I should see "event.catalog.category.seeSheet"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/catalog/1/sheet/2"
    And I should see "event.catalog.sheet.meetingRequest"
    Then I follow "event.catalog.sheet.meetingRequest"
    And the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/catalog/1/sheet/2/meeting/request/from/1"
    And I check "Dutest Jean"
    And I fill in the following:
      | form.meeting_request_create.children.description.label | This is a test |
    And I press "form.meeting_request_create.children.submit.label"
    And the response status code should be 200
    And I should see "flash.meeting_request.create.success"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/meeting/request"
    And the response status code should be 200
    And I should see "event.meeting.request.state.from.sent"
    And I should see "Oale"
    And I should see "Dutest Jean"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/logout"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And the response status code should be 200
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And I fill in "form.login.children.username.label" with "test-3@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then I follow "event.link.see_meeting_proposition"
    And the response status code should be 200
    And I should see "Elao"
    Then I follow "event.meeting.listProposition.refuse"
    And the response status code should be 200
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/2/meeting/proposition/refused/1"
    And I fill in the following:
      | form.meeting_request_refuse.children.refuseMessage.label | Sorry I can't |
    Then I press "form.meeting_request_refuse.children.submit.label"
    And I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/2/meeting/proposition"
    And I should see "flash.meeting_request.refused.success"
    And I should see "event.meeting.request.state.to.refused"
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/logout"
    Then I should be on "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/"
    And the response status code should be 200
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/login"
    And the response status code should be 200
    And I fill in "form.login.children.username.label" with "test@test.com"
    And I fill in "form.login.children.password.label" with "p@ssw0rd"
    And I press "form.login.children.submit.label"
    Then the response status code should be 200
    Then I go to "http://rdv-carnot-2016.vimeet.proximum.dev/app_test.php/fr/sheet/1/meeting/request"
    And I should see "event.meeting.request.state.from.refused"
    And I should see "Sorry I can't"
