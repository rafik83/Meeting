Feature: Create Operator
  I need to be able to create an Operator

  Background: Re-init the database and load the fixtures
    Given the database is empty
    And the following fixtures files are loaded:
      | app/Template.yml |
      | app/Event.yml    |
      | Admins.yml       |
    Given I am logged with "test2@test.com" on admin

  Scenario: I can create an Operator with the events of the organizer
    When I go to this page "/admin/fr/event"
    And I follow "admin.operator_list.link"
    Then I should be on this page "/admin/fr/operator"
    And I follow "admin.operator_list.action.create"
    Then I should be on this page "/admin/fr/operator/create"
    Then I fill in the following:
      | form.create_operator.children.email.label     | toto@toto.fr |
      | form.create_operator.children.lastname.label  | Toto         |
      | form.create_operator.children.firstname.label | Tata         |
    And I press "form.create_operator.children.submit.label"
    Then I should be on this page "/admin/fr/operator"
    And I should see "flash.admin.operator.create.success"
    Given I am logged with "toto@toto.fr" on admin
    When I go to this page "/admin/fr/event"
    Then I should see "Les rendez-vous CARNOT 2016"
