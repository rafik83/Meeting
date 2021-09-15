@admin @mail

Feature: Operator Activate Account
  I need to be able to activate my account

  Scenario: I can activate my account
    Given the database is purged
    And the event "RDV Carnot 2019" is created
    And there is a sheet
    And the admin "organiser@test.com" with role "ROLE_ORGANIZER" is created
    And this admin has an activation token "127c926781d058ccca7d7f445b3010c0"
    And this admin can access this event
    When I go to this page "/fr/activate-account/127c926781d058ccca7d7f445b3010c0"
    Then I fill in the following:
      | form.admin_activate_account_password.children.password.children.first.label  | tructruc |
      | form.admin_activate_account_password.children.password.children.second.label | tructruc |
    And I press "form.admin_activate_account_password.children.submit.label"
    Then I should be on this page "/fr/event"
    And I should see "flash.admin.activate_account.success"

  Scenario: I can add an operator and activate the account
    Given the database is purged
    And the event "RDV Carnot 2019" is created
    And there is a sheet
    And the admin "organizer@test.com" with role "ROLE_ORGANIZER" is created
    And this admin can access this event
    And I am logged with this admin
    When I go to this page "/fr/event"
    And I follow "admin.operator_list.link"
    Then I should be on this page "/fr/operator"
    And I follow "admin.operator_list.action.create"
    Then I should be on this page "/fr/operator/create"
    Then I fill in the following:
      | form.create_operator.children.email.label     | toto@toto.fr |
      | form.create_operator.children.lastname.label  | Toto         |
      | form.create_operator.children.firstname.label | Tata         |
    And I check "RDV Carnot 2019"
    And I press "form.create_operator.children.submit.label"
    And the "admin.account_activated" mail should be sent to "toto@toto.fr" from "vimeet"
    And the "admin.account_activated" mail should contain the link "http://admin.vimeet.proximum/fr/activate-account"
    Then I follow the "http://admin.vimeet.proximum/fr/activate-account" link in the "admin.account_activated" mail
    And the response status code should be 200
    Then I fill in the following:
      | form.admin_activate_account_password.children.password.children.first.label  | tructruc |
      | form.admin_activate_account_password.children.password.children.second.label | tructruc |
    And I press "form.admin_activate_account_password.children.submit.label"
    Then I should be on this page "/fr/event"
    And I should see "flash.admin.activate_account.success"
