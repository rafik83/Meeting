@admin @partner

Feature: Partner available features

  Scenario: I can see the sheet participation list
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
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Order.yml             |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Event.yml           |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Nomenclature.yml    |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Template.yml        |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Product.yml         |
      | @InfrastructureBundle/DataFixtures/ORM/RdvCarnot2016-Type.yml            |
      | Partner.yml                                                              |
    And elastica is populate
    And I am logged with "partner@proximumgroup.com" on admin
    When I go to this page "/admin/fr/event"
    Then I should see "ASD Days"
    And I should see "Les rendez-vous CARNOT 2016"
    When I follow "ASD Days"
    Then I should be on this page "/admin/fr/event/1/sheet"
    And I should see "admin.sheet.title"
    When I check radio "sheet_batch_ids_1"
    Then I should see "form.sheet_batch.children.accept.label"
    And I should see "admin.sheet.validate"
    And I should see "admin.sheet.commercialFollowUp"
    And I should not see "form.sheet_batch.children.addCatalog.label"
    And I should not see "form.sheet_batch.children.removeCatalog.label"
    And I should not see "form.sheet_batch.children.enable.label"
    And I should not see "form.sheet_batch.children.disable.label"

  Scenario: I can see sheet detail
    Given I am logged with "partner@proximumgroup.com" on admin
    When I go to "/admin/fr/event/1/sheet"
    Then I go to "/admin/fr/event/1/sheet/1"
    And I should see "admin.sheet.details.dashboard.title"
    And I should see "admin.sheet.details.historic.title"
    And I should not see "admin.sheet.details.orders_and_transactions.title"
    
  Scenario: Partner can only access to sheet, account and login/logout
    Given I am logged with "partner@proximumgroup.com" on admin
    When I go to "/admin/fr/event/1/sheet"
    Then the response status code should be 200
    When I go to "/admin/fr/event/1/sheet/1"
    Then the response status code should be 200
    When I go to "/admin/fr/account"
    Then the response status code should be 200

  Scenario: Partner can't access other page
    Given I am logged with "partner@proximumgroup.com" on admin
    When I go to "/admin/fr/event/1/practical-info" without checking
    Then the response status code should be 403
    When I go to "/admin/fr/event/1/category" without checking
    Then the response status code should be 403
    When I go to "/admin/fr/event/1/type" without checking
    Then the response status code should be 403
    When I go to "/admin/fr/event/1/order" without checking
    Then the response status code should be 403
    When I go to "/admin/fr/event/1/meeting" without checking
    Then the response status code should be 403
