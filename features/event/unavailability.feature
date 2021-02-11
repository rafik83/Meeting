@event @agenda @unavailability
Feature: Unavailability
  As a participant, I can add and remove an unavailability

  Scenario: I can add an unavailability
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays.vimeet.proximum"
    And this event happens from september 1 to september 2 2020
    And the agenda is open
    And there is a type in this event
    And this type has unavailability management enabled

    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet for this type with the title "Aanera"
    And this sheet has confirmed attendance
    And there is a participant for this sheet and this user

    And I am logged with "user_asddays_1@proximum.com" on front

    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    When I go to this page "/fr/sheet/1/agenda"
    Then I should be on this page "/fr/sheet/1/agenda/participant/1"
    And I should see "agenda.title"
    And I should see "Mardi 1 septembre 2020"
    And I should not see "unavailability.title"
    And I follow "agenda.unavailability.add"
    And I should be on this page "/fr/sheet/1/agenda/participant/1/unavailability/create"
    And I should see "form.create_unavailability.children.submit.label"
    And I should see "agenda.unavailability.back"
    Then I fill in the following:
      #Mardi 1 septembre 2020
      | form.create_unavailability.children.day.label | 0   |
      | create_unavailability_time_begin_hour         | 11  |
      | create_unavailability_time_begin_minute       | 30  |
      | create_unavailability_time_end_hour           | 13  |
      | create_unavailability_time_end_minute         | 45  |
    And I press "form.create_unavailability.children.submit.label"
    Then I should be on this page "/fr/sheet/1/agenda/participant/1"
    And I should see "unavailability.title"

  Scenario: I can remove an unavailability
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And I go to this page "/fr/sheet/1/agenda"
    And show last response
    And I should see "unavailability.title"
    When I press "cancelUnavailability"
    Then I should be on this page "/fr/sheet/1/agenda/participant/1"
    And I should not see "unavailability.title"

  Scenario: I can add comment to an unavailability
    Given there is an event with domain "asddays.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And I go to this page "/fr"
    Then I should be on this page "/fr/sheet/1"
    When I go to this page "/fr/sheet/1/agenda"
    Then I should be on this page "/fr/sheet/1/agenda/participant/1"
    And I should see "agenda.title"
    And I should see "Mardi 1 septembre 2020"
    And I should not see "unavailability.title"
    And I follow "agenda.unavailability.add"
    And I should be on this page "/fr/sheet/1/agenda/participant/1/unavailability/create"
    And I should see "form.create_unavailability.children.submit.label"
    And I should see "agenda.unavailability.back"
    Then I fill in the following:
      #Mardi 1 septembre 2020
      | form.create_unavailability.children.day.label | 0   |
      | create_unavailability_time_begin_hour         | 11  |
      | create_unavailability_time_begin_minute       | 30  |
      | create_unavailability_time_end_hour           | 13  |
      | create_unavailability_time_end_minute         | 45  |
      | create_unavailability_message                 | "Concert de Patrick sebastien" |
    And I press "form.create_unavailability.children.submit.label"
    Then I should be on this page "/fr/sheet/1/agenda/participant/1"
    And I should see "unavailability.title"
    And I should see "Concert de Patrick sebastien"
