@admin @event
Feature: See and update the days of an event

  Scenario: See days of an event
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | Admin.yml                                                                |
    Given I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event"
    And I follow "admin.schedule.link"
    Then I should be on this page "/admin/fr/event/1/schedule/slots"
    And I should see "12 oct. 2016"
    And I should see "10:00"

  Scenario: Update days of an event
    Given I am logged with "test@test.com" on admin
    When I go to this page "/admin/fr/event"
    And I go to this page "/admin/fr/event/1/schedule/days"
    And I fill in the following:
    | form.admin_schedule_days.children.days.prototype.children.startTime.label | 12/10/2016 22:00 |
    | form.admin_schedule_days.children.days.prototype.children.endTime.label   | 12/10/2016 08:00 |
    And I press "form.admin_schedule_days.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/schedule/days"
    And I should see "validators.schedule_day.startTimeMustBeBeforeEndTime"
    And I fill in the following:
    | form.admin_schedule_days.children.days.prototype.children.startTime.label | 12/10/2016 22:00 |
    | form.admin_schedule_days.children.days.prototype.children.endTime.label   | 18/10/2016 08:00 |
    And I press "form.admin_schedule_days.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/schedule/days"
    And I should see "validators.schedule_day.shouldBeTheSameDay"
    And I fill in the following:
    | form.admin_schedule_days.children.days.prototype.children.startTime.label | 12/10/2016 11:00 |
    | form.admin_schedule_days.children.days.prototype.children.endTime.label   | 12/10/2016 19:00 |
    And I press "form.admin_schedule_days.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/schedule/slots"
    And I should see "flash.schedule.days.success"
    And I should see "11:00"
    And I should see "19:00"
