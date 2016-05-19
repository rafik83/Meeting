Feature: Manage templates
  As an Admin, I need to be able to add, update, duplicate, see templates

  Scenario: see list of templates
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    And I follow "admin.template.sheet.link"
    And I should be on this page "/admin/fr/template/sheet"
    And I should see "Template ASD Days"
    Then I go to this page "/admin/fr/event"
    And I follow "admin.template.registration.link"
    Then I should be on this page "/admin/fr/template/registration"
    And I should see "Inscription Template de base"

  Scenario: edit Registration Template
    Given I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/template/registration"
    And I should see "Inscription Template de base"
    Then I follow "admin.template.registration.table.content.edit"
    And I should be on this page "/admin/fr/template/registration/1/fr"
    Then I fill in "template_registration_update_title" with "Template updated"
    And I press "template_registration_update_submit"
    Then I should be on this page "/admin/fr/template/registration"
    And I should see "Template updated"
    And I should not see "Inscription Template de base"
