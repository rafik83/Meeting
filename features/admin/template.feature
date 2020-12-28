@admin

Feature: Manage templates
  As an Admin, I need to be able to add, update, duplicate, see templates

  Scenario: See list of templates
    Given the database is purged
    And there is a turnover nomenclature
    And there is a registration template
    And there is a sheet template
    And I am logged as admin
    And I am on this page "/fr/event"
    And I follow "admin.template.sheet.link"
    And I should be on this page "/fr/template/sheet"
    And I should see "SheetTemplate"
    Then I go to this page "/fr/event"
    And I follow "admin.template.registration.link"
    Then I should be on this page "/fr/template/registration"
    And I should see "RegistrationTemplate"

  Scenario: Edit json Registration Template
    Given I am logged as admin
    And I am on this page "/fr/template/registration"
    And I should see "RegistrationTemplate"
    Then I follow "admin.template.registration.table.content.editJson"
    And I should be on this page "/fr/template/registration/1/fr/json"
    Then I fill in "template_registration_update_title" with "Template updated"
    And I press "template_registration_update_submit"
    Then I should be on this page "/fr/template/registration/1/fr/json"
    And I should see "flash.admin.template.registration.update.success"

  Scenario: Edit Sheet Template
    Given I am logged as admin
    And I am on this page "/fr/template/sheet"
    And I should see "SheetTemplate"
    When I follow "admin.template.action.edit"
    Then I should be on this page "/fr/template/sheet/1/fr"
    And I should see "SheetTemplate"
    And I should see "template.object.editable-text"
    And I should see "template.object.image"
    And I should see "template.object.tags"
    And I should see "template.object.collection"
    And I should see "template.object.nomenclature"
    And I should see "template.object.text"
    And I should see "template.object.media"
    And I should see "template.participant.title"

  Scenario: I can edit the template preview
    Given I am logged as admin
    And I am on this page "/fr/template/sheet"
    And I should see "SheetTemplate"
    And I should see "admin.template.action.preview"
    When I follow "admin.template.action.preview"
    Then I should be on this page "/fr/template/sheet/1/preview"
    And I should see "admin.template.preview.title"
    When I press "form.sheet_template_preview.children.submit.label"
    Then I should be on this page "/fr/template/sheet/1/preview"
    And I should see "flash.template.preview.update.success"
