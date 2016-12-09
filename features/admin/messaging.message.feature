@admin
@admin-event
Feature: See, create and update an emailing message
  I need to be able to see, create and update an emailing message

  Scenario: See emailing message
    Given the database is purged
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Messaging-Message.yml |
      | Admin.yml                                                                |
    Given I am logged with "test@test.com" on admin
    When I go to this page "/admin/en/event"
    Then I should see "ASD Days"
    When I follow "admin.messaging.message.link"
    Then I should see "dummy-name"

  Scenario: Create emailing message
    Given I am logged with "test@test.com" on admin
    Then I go to this page "/admin/en/event/1/messaging/messages"
    And I follow "admin.messaging.message.create.title"
    And I should see "admin.messaging.message.create.title"
    Then I fill in the following:
      | form.messaging_create_message.children.name.label      | foo |
      | form.messaging_create_message.children.subject.label   | bar |
      | form.messaging_create_message.children.content.label   | baz |
    And I press "form.messaging_create_message.children.submit.label"
    And I should be on this page "/admin/en/event/1/messaging/messages"
    And I should see "flash.messaging.message.create.success"
    And I should see "foo"

  Scenario: Update emailing message
    Given I am logged with "test@test.com" on admin
    Then I go to this page "/admin/en/event/1/messaging/messages"
    And I follow "admin.messaging.message.list.edit_link"
    And I should see "admin.messaging.message.edit.title"
    Then I fill in the following:
      | form.messaging_update_message.children.name.label      | edited-foo |
      | form.messaging_update_message.children.subject.label   | bar        |
      | form.messaging_update_message.children.content.label   | edited-baz |
    And I press "form.messaging_update_message.children.submit.label"
    And I should be on this page "/admin/en/event/1/messaging/messages"
    And I should see "flash.messaging.message.update.success"
    And I should see "edited-foo"
