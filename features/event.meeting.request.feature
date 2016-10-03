@event
@meeting
Feature: Meeting Request / Proposition
  I need to be able to see the meeting request and proposition

  Scenario: I can see my meeting requests
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Sheet.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Participant.yml     |
      | @InfrastructureBundle/DataFixtures/ORM/Meeting/RdvCarnot2016-Request.yml |
    And I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
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
    And I should see "L'ONERA est le centre français de la recherche aéronautique, spaciale et de défense."
    And I should see "catalog.complete_sheet"
    And I should see "Exposant"

  Scenario: I can see my meeting proposition
    Given I am logged with "test@elao.com" on event "http://rdv-carnot-2016.vimeet.proximum.dev"
    And I go to this page "/fr/sheet/1/meeting/proposition"
