@event
@meeting
Feature: Meeting Request / Proposition
  I need to be able to see the meeting request and proposition

  Scenario: I can see my meeting requests
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Category.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Rule.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/RdvCarnot2016-Request.yml |
    And I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum"
    And I go to this page "/fr"
    When I go to this page "/fr/sheet/1/meeting/request"
    Then I should see "form.search.meeting.state.label"
    And I should see "form.search.meeting.state.all"
    And I should see "form.search.meeting.state.approved"
    And I should see "form.search.meeting.state.sent"
    And I should see "form.search.meeting.state.refused"
    And I should see "form.search.meeting.state.receive"
    And I should see "form.search.orderBy.label"
    And I should see "form.search.orderBy.alphabetical"
    And I should see "form.search.orderBy.createdAt"
    And I should see "form.search.type.label"
    And the "type_0" checkbox should be checked
    And the "type_1" checkbox should be checked
    And the "type_2" checkbox should be checked
    And I should not see field "type_3"
    And I should not see field "type_4"
    And I should see "Exposant"
    And I should see "Investisseur"
    And I should see "catalog.meeting_request.proposition.approved"
    And I should see "catalog.meeting_request.pending"
    And I should see "catalog.meeting_request.request.refused"
    And I should see "catalog.meeting_request.proposition.refused"
    And I should see "catalog.meeting_request.approve"
    And I should see "catalog.meeting_request.refuse"
    And I should see "catalog.complete_sheet"
    And I should see "Exposant"

  Scenario: I can filter by participant type (not see Exposant type)
    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum"
    And I go to this page "/fr"
    When I go to this page "/fr/sheet/1/meeting/request"
    Then I uncheck "type_2"
    And I go to this page "/fr/sheet/1/meeting/request?type%5B%5D=3&type%5B%5D=4&type%5B%5D=2&type%5B%5D=5"
    And I should not see "Exposant" in the "footer" element
