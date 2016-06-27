@admin

Feature: Handle Operator
  I need to be able to create, list and update an Operator

  Scenario: I can create an Operator with the events of the organizer
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | Admins.yml                                                               |
    And I am logged with "test2@test.com" on admin
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

  Scenario: I can edit an Operator
    Given I am logged with "test2@test.com" on admin
    When I go to this page "/admin/fr/event"
    And I follow "admin.operator_list.link"
    Then I should be on this page "/admin/fr/operator"
    And I should see "toto@toto.fr"
    And I should see "Toto"
    And I should see "Tata"
    Then I follow "admin.operator_list.table.content.update"
    And I should be on this page "/admin/fr/operator/update/4"
    And I uncheck "Les rendez-vous CARNOT 2016"
    And I press "form.update_operator.children.submit.label"
    Then I should be on this page "/admin/fr/operator"
    And I should see "flash.admin.operator.update.success"
    And I should not see "toto@toto.fr"


