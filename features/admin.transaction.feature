@admin

Feature: Admin Transaction
  I need to be able to manage the transaction for a participant

  Scenario: I can see a list of transactions
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event"
    When I follow "admin.sheet.link"
    Then the response status code should be 200
    And I should be on this page "/admin/fr/event/1/sheet"
    Then I go to "/admin/fr/event/1/sheet/1"
    Then the response status code should be 200
    And I should see "admin.sheet.details.transactions"
    And I should see "admin.transaction.state.pending"
    And I should see "admin.transaction.state.paid"

  Scenario: I can add a new transaction
    Given the database is empty
    And the following fixtures files are loaded:
      | @InfrastructureBundle/DataFixtures/ORM/Nomenclature.yml                  |
      | @InfrastructureBundle/DataFixtures/ORM/Template/SheetTemplate.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/Template/RegistrationTemplate.yml |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Event.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Nomenclature.yml      |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Product.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Template.yml          |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Type.yml              |
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Sheet.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/User.yml                          |
      | Admin.yml                                                                |
    And I am logged with "test@test.com" on admin
    And I go to "/admin/fr/event"
    When I follow "admin.sheet.link"
    Then the response status code should be 200
    And I should be on this page "/admin/fr/event/1/sheet"
    Then I go to "/admin/fr/event/1/sheet/1"
    Then the response status code should be 200
    And I follow "admin.transaction.add"
    Then I should be on this page "/admin/fr/event/1/sheet/1/transaction/create"
    And I fill in the following:
      | form.create_transaction.children.amount.label    | 25             |
      | form.create_transaction.children.mode.label      | bank_card      |
      | form.create_transaction.children.reference.label | transaction_03 |
    Then I check the "form.transaction.children.state.paid" radio
    Then I press "form.create_transaction.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/sheet/1"
    And I should see "flash.admin.transaction.create.success"


