@admin

Feature: Manage Admin
  I need to be able to manage Admin and Organizer

  Scenario: I can create an Organizer link to an event
    Given the database is purged
    And the super admin "test@test.com" is created
    And the event "Les rendez-vous CARNOT 2016" is created
    And I am logged with "test@test.com" on admin
    When I go to this page "/fr/event"
    And I follow "admin.admin_list.link"
    Then I should be on this page "/fr/admin"
    And I should see "Teemiv"
    And I should see "admin.admin_list.table.content.super_admin.events.all"
    Then I follow "admin.admin_list.action.create"
    And I should be on this page "/fr/admin/create"
    Then I fill in the following:
      | form.create_admin.children.email.label     | organizer@organizer.com |
      | form.create_admin.children.password.label  | 123456789-Vimeet        |
      | form.create_admin.children.lastname.label  | Toto                    |
      | form.create_admin.children.firstname.label | Tata                    |
    And I select "form.create_admin.role.organizer" from "form.create_admin.children.role.label"
    And I check "Les rendez-vous CARNOT 2016"
    And I press "form.create_admin.children.submit.label"
    Then I should be on this page "/fr/admin"
    And I should see "flash.admin.admin.create.success"
    And I should see "Les rendez-vous CARNOT 2016"

  Scenario: I can create an Organizer without events
    Given I am logged with "test@test.com" on admin
    When I go to this page "/fr/event"
    And I follow "admin.admin_list.link"
    Then I should be on this page "/fr/admin"
    And I should see "Teemiv"
    And I should see "admin.admin_list.table.content.super_admin.events.all"
    And I should see ""
    Then I follow "admin.admin_list.action.create"
    And I should be on this page "/fr/admin/create"
    Then I fill in the following:
      | form.create_admin.children.email.label     | disabled@organizer.com |
      | form.create_admin.children.password.label  | 123456789-Vimeet2      |
      | form.create_admin.children.lastname.label  | Toto                   |
      | form.create_admin.children.firstname.label | Tata                   |
    And I select "form.create_admin.role.organizer" from "form.create_admin.children.role.label"
    And I press "form.create_admin.children.submit.label"
    Then I should be on this page "/fr/admin"
    And I should see "flash.admin.admin.create.success"
    And I should see "admin.admin_list.table.content.admin.events.none"

  Scenario: I can create an Admin link to an event
    Given I am logged with "test@test.com" on admin
    When I go to this page "/fr/event"
    And I follow "admin.admin_list.link"
    Then I should be on this page "/fr/admin"
    And I should see "Teemiv"
    And I should see "admin.admin_list.table.content.super_admin.events.all"
    And I should see ""
    Then I follow "admin.admin_list.action.create"
    And I should be on this page "/fr/admin/create"
    Then I fill in the following:
      | form.create_admin.children.email.label     | event-admin@organizer.com |
      | form.create_admin.children.password.label  | 123456789-Vimeet3         |
      | form.create_admin.children.lastname.label  | Toto                      |
      | form.create_admin.children.firstname.label | Tata                      |
    And I select "form.create_admin.role.super_admin" from "form.create_admin.children.role.label"
    And I check "Les rendez-vous CARNOT 2016"
    And I press "form.create_admin.children.submit.label"
    Then I should be on this page "/fr/admin"
    And I should see "flash.admin.admin.create.success"
    And I should see "Les rendez-vous CARNOT 2016"

  Scenario: I can edit an Organizer
    Given I am logged with "test@test.com" on admin
    And I go to this page "/fr/admin"
    And I should see "organizer@organizer.com"
    And I go to this page "/fr/admin/update/2"
    And I should see "admin.update_admin.title"
    And I fill in the following:
      | form.update_admin.children.email.label     | organizer-updated@organizer.com |
      | form.update_admin.children.lastname.label  | TRUC                            |
      | form.update_admin.children.firstname.label | MUCHE                           |
    And I uncheck "Les rendez-vous CARNOT 2016"
    And I press "form.update_admin.children.submit.label"
    Then I should be on this page "/fr/admin"
    And I should see "flash.admin.admin.update.success"
    And I should see "admin.role.ROLE_ORGANIZER"
    And I should see "admin.admin_list.table.content.admin.events.none"
    And I should see "TRUC"
    And I should see "MUCHE"
    And I should see "organizer-updated@organizer.com"
    And I should not see "organizer@organizer.com"
