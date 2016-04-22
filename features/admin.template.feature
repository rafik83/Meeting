Feature: Template
  I need to be able to add, update, duplicate, see template

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml          |
      | app/Event.yml             |
      | app/Type.yml              |
      | app/Category.yml          |
      | Admin.yml                 |
      | template/Sheet.yml        |
      | template/Registration.yml |
    And I am logged with "test@test.com" on admin

  Scenario: see list of template
    Given I am on this page "/admin/fr/event"
    And I follow "admin.template_list.link"
    Then I should be on this page "/admin/fr/template"
    And I should see "admin.template.sheet.link"
    And I should see "admin.template.registration.link"
    Then I follow "admin.template.sheet.link"
    And I should be on this page "/admin/fr/template/sheet"
    And I should see "Template ASD Days"
    Then I go to this page "/admin/fr/template"
    And I follow "admin.template.registration.link"
    Then I should be on this page "/admin/fr/template/registration"
    And I should see "Inscription Template ASD Days"

  Scenario: edit Registration Template
    Given I am on this page "/admin/fr/template/registration"
    And I should see "Inscription Template ASD Days"
    Then I follow "admin.template.registration.table.content.edit"
    And I should be on this page "/admin/fr/template/registration/1/fr"
    Then I fill in "template_registration_update_title" with "Inscription Base Template ASD DAYS"
    And I press "template_registration_update_submit"
    Then I should be on this page "/admin/fr/template/registration"
    And I should see "Inscription Base Template ASD DAYS"
    And I should not see "Inscription Template ASD Days"
