@admin

Feature: Handle Operator
  I need to be able to create, list and update an Operator

  Scenario: I can create an Operator with the events of the organizer
    Given the database is purged
    And the admin "test2@test.com" with role "ROLE_ORGANIZER" is created
    And the event "Les rendez-vous CARNOT 2016" is created
    And this admin can access this event
    And I am logged with "test2@test.com" on admin
    And I go to this page "/fr/event"
    When I go to this page "/fr/event/past"
    And I follow "admin.operator_list.link"
    Then I should be on this page "/fr/operator"
    And I follow "admin.operator_list.action.create"
    Then I should be on this page "/fr/operator/create"
    Then I fill in the following:
      | form.create_operator.children.email.label     | toto@toto.fr |
      | form.create_operator.children.lastname.label  | Toto         |
      | form.create_operator.children.firstname.label | Tata         |
    And I check "Les rendez-vous CARNOT 2016"
    And I press "form.create_operator.children.submit.label"
    Then I should be on this page "/fr/operator"
    And I should see "flash.admin.operator.create.success"
    Given I am logged with "toto@toto.fr" on admin
    When I go to this page "/fr/event"
    Then I should see "Les rendez-vous CARNOT 2016"

  Scenario: I can edit an Operator
    Given I am logged with "test2@test.com" on admin
    When I go to this page "/fr/event"
    And I follow "admin.operator_list.link"
    Then I should be on this page "/fr/operator"
    And I should see "toto@toto.fr"
    And I should see "Toto"
    And I should see "Tata"
    Then I follow "admin.operator_list.table.content.update"
    And I should be on this page "/fr/operator/update/2"
    And I uncheck "Les rendez-vous CARNOT 2016"
    And I press "form.update_operator.children.submit.label"
    Then I should be on this page "/fr/operator"
    And I should see "flash.admin.operator.update.success"
    And I should not see "toto@toto.fr"


