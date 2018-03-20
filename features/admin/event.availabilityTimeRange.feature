@admin @event @availability-time-range
Feature: See and update the availability time ranges of an event

  Scenario: Create availability time range of an event
    Given the database is purged
    And the event "Best of web" is created
    And this event occurs the "2018-10-12" from "08:00" to "18:00"
    And the super admin "test@test.com" is created
    And I am logged with this admin
    And I am on the homepage of the admin
    When I go to this page "/fr/event/1/availability-time-range"
    Then I should see "admin.zero-result"
    And I follow "admin.availabilityTimeRange.create.title"
    And I should be on this page "/fr/event/1/availability-time-range/create"
    And I fill in the following:
      | form.availability_time_range_create.children.name.label  | Plage de détente |
      | form.availability_time_range_create.children.begin.label | 12/10/2018 12:00 |
      | form.availability_time_range_create.children.end.label   | 12/10/2018 13:00 |
    When I press "form.availability_time_range_create.children.submit.label"
    Then I should be on this page "/fr/event/1/availability-time-range"
    And I should see "Plage de détente"
    And I should see "12 oct. 2018 12:00"
    And I should see "12 oct. 2018 13:00"
