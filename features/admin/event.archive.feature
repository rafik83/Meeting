@admin @admin-event

Feature: Archive an event
  I need to be able to archive an event

  Scenario: Archive an event
    Given the database is purged
    And the event "Super Event" is created
    And this event occurs the "2017-04-04" from "09:00" to "18:00"
    And the super admin "test_archive@example.net" is created
    And I am logged with this admin
    When I am on the homepage of the admin
    And I go to this page "/fr/event/past"
    Then I should see "super-event.vimeet.proximum"
    When I go to this page "/fr/event/archived"
    Then I should not see "super-event-2017.vimeet.proximum"
    When I go to this page "/fr/event/1/archive"
    Then I should see "form.archive_un_archive.children.archive.label"
    When I press "form.archive_un_archive.children.archive.label"
    Then I should be on this page "/fr/event/1/archive"
    And I should see "flash.admin.event.archive.success"
    When I go to this page "/fr/event/archived"
    Then I should see "super-event-2017.vimeet.proximum"
    When I go to this page "/fr/event/past"
    Then I should not see "super-event-2017.vimeet.proximum"
