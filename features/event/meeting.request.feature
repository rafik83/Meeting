@event
@meeting
Feature: Meeting Request / Proposition
  I need to be able to see the meeting request and proposition

  Scenario: I can see my meeting requests
    Given the database is purged
    And the event "RDV Carnot" is created
    And the domain for this event is "rdv-carnot.vimeet.proximum"
    And the catalog visibility is configured
    And the catalog is open since "2020-09-10 10:00:00"
    And there is a type "Fournisseur" in this event
    And this type is visible in catalog

    And the user "test@fairness.coop" is created
    And there is a sheet for this type with the title "Fairness"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And this sheet is in catalog
    And there is a type "Donneur d'ordre" in this event
    And this type is visible in catalog

    And the user "user_1@proximum.com" is created
    And there is a sheet for this type with the title "Company #1"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And this sheet is in catalog
    And there is a request between "Company #1" and "Fairness"
    And this request has been approved

    And there is a type "Exposant" in this event
    And this type is visible in catalog
    And the user "user_2@proximum.com" is created
    And there is a sheet for this type with the title "Company #2"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And this sheet is in catalog
    And there is a request between "Fairness" and "Company #2"

    And the user "user_3@proximum.com" is created
    And there is a sheet for this type with the title "Company #3"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And this sheet is in catalog
    And there is a request between "Company #3" and "Fairness"
    And this request has been refused

    And the user "user_4@proximum.com" is created
    And there is a sheet for this type with the title "Company #4"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And this sheet is in catalog
    And there is a request between "Fairness" and "Company #4"
    And this request has been refused

    And the user "user_5@proximum.com" is created
    And there is a sheet for this type with the title "Company #5"
    And there is a participant for this sheet and this user
    And this sheet is validated
    And this sheet is in catalog
    And there is a request between "Company #5" and "Fairness"

    And elastica is populate
    And I am logged with "test@fairness.coop" on front

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
    And I should see "Exposant"
    And I should see "Donneur d'ordre"
    And I should see "catalog.meeting_request.proposition.approved"
    And I should see "catalog.meeting_request.pending"
    And I should see "catalog.meeting_request.request.refused"
    And I should see "catalog.meeting_request.proposition.refused"
    And I should see "catalog.meeting_request.approve"
    And I should see "catalog.meeting_request.refuse"
    And I should see "catalog.complete_sheet"

  Scenario: I can filter by participant type (not see Exposant type)
    Given there is an event with domain "rdv-carnot.vimeet.proximum"
    And I am logged with "test@fairness.coop" on front

    When I go to this page "/fr/sheet/1/meeting/request?type[]=0"
    Then I should not see "Exposant" in the "footer" element
