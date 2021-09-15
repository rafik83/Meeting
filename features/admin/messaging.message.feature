@admin
@admin-event
@messaging
Feature: See, create and update an emailing message
  I need to be able to see, create and update an emailing message

  Scenario: See emailing message
    Given the database is purged
    And the event "La chasse aux champignons" is created
    And there is an emailing message with the name "10 astuces pour réussir sa cueillette" for this event
    And I am logged as admin
    When I go to this page "/en/event/1"
    And I follow "admin.messaging.message.link"
    Then I should see "10 astuces pour réussir sa cueillette"

  Scenario: Create emailing message
    Given I am logged as admin
    Then I go to this page "/en/event/1/messaging/messages"
    And I follow "admin.messaging.message.create.title"
    And I should see "admin.messaging.message.create.title"
    Then I fill in the following:
      | form.messaging_create_message.children.name.label | foo        |
      | messaging_create_message_translations_en_subject  | bar        |
      | messaging_create_message_translations_en_content  | baz        |
      | messaging_create_message_translations_fr_subject  | subject_fr |
      | messaging_create_message_translations_fr_content  | content_fr |
    And I press "form.messaging_create_message.children.submit.label"
    And I should be on this page "/en/event/1/messaging/messages"
    And I should see "flash.messaging.message.create.success"
    And I should see "foo"

  Scenario: Update emailing message
    Given I am logged as admin
    Then I go to this page "/en/event/1/messaging/messages"
    And I follow "admin.messaging.message.list.edit_link"
    And I should see "admin.messaging.message.edit.title"
    Then I fill in the following:
      | form.messaging_create_message.children.name.label | edited-foo |
      | messaging_create_message_translations_en_subject  | bar        |
      | messaging_create_message_translations_en_content  | baz        |
      | messaging_create_message_translations_fr_subject  | subject_fr |
      | messaging_create_message_translations_fr_content  | content_fr |
    When I press "messaging_create_message_submit"
    Then I should be on this page "/en/event/1/messaging/messages"
    And I should see "flash.messaging.message.update.success"
    And I should see "edited-foo"
