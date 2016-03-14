Feature: Create Admin
  I need to be able to create Admin and Organizer

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | Admin.yml        |
    Given I am logged with "test@test.com" on admin

  Scenario: I can create an Organizer link to an event
    When I go to this page "/admin/fr/event"
    And I follow "admin.admin_list.link"
    Then I should be on this page "/admin/fr/admin"
    And I should see "DUPONT"
    And I should see "admin.admin_list.table.content.super_admin.events.all"
    And I should see ""
    Then I follow "admin.admin_list.action.create"
    And I should be on this page "/admin/fr/admin/create"
    Then I fill in the following:
      | form.create_admin.children.email.label     | toto@toto.fr |
      | form.create_admin.children.password.label  | 123456789    |
      | form.create_admin.children.lastname.label  | Toto         |
      | form.create_admin.children.firstname.label | Tata         |
    And I select "form.create_admin.role.organizer" from "form.create_admin.children.role.label"
    And I check "Les rendez-vous CARNOT 2016"
    And I press "form.create_admin.children.submit.label"
    Then I should be on this page "/admin/fr/admin"
    And I should see "flash.admin.admin.create.success"
    And I should see "Les rendez-vous CARNOT 2016"

  Scenario: I can create an Organizer without events
    When I go to this page "/admin/fr/event"
    And I follow "admin.admin_list.link"
    Then I should be on this page "/admin/fr/admin"
    And I should see "DUPONT"
    And I should see "admin.admin_list.table.content.super_admin.events.all"
    And I should see ""
    Then I follow "admin.admin_list.action.create"
    And I should be on this page "/admin/fr/admin/create"
    Then I fill in the following:
      | form.create_admin.children.email.label     | toto@toto.fr |
      | form.create_admin.children.password.label  | 123456789    |
      | form.create_admin.children.lastname.label  | Toto         |
      | form.create_admin.children.firstname.label | Tata         |
    And I select "form.create_admin.role.organizer" from "form.create_admin.children.role.label"
    And I press "form.create_admin.children.submit.label"
    Then I should be on this page "/admin/fr/admin"
    And I should see "flash.admin.admin.create.success"
    And I should see "admin.admin_list.table.content.admin.events.none"

  Scenario: I can create an Admin link to an event
    When I go to this page "/admin/fr/event"
    And I follow "admin.admin_list.link"
    Then I should be on this page "/admin/fr/admin"
    And I should see "DUPONT"
    And I should see "admin.admin_list.table.content.super_admin.events.all"
    And I should see ""
    Then I follow "admin.admin_list.action.create"
    And I should be on this page "/admin/fr/admin/create"
    Then I fill in the following:
      | form.create_admin.children.email.label     | toto@toto.fr |
      | form.create_admin.children.password.label  | 123456789    |
      | form.create_admin.children.lastname.label  | Toto         |
      | form.create_admin.children.firstname.label | Tata         |
    And I select "form.create_admin.role.super_admin" from "form.create_admin.children.role.label"
    And I check "Les rendez-vous CARNOT 2016"
    And I press "form.create_admin.children.submit.label"
    Then I should be on this page "/admin/fr/admin"
    And I should see "flash.admin.admin.create.success"
    And I should see "Les rendez-vous CARNOT 2016"
