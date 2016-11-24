@admin
@order
Feature: Find a sheet with an order numero
  As an Admin, I need to be able to find a sheet with the order numero I have

  Scenario: Find the sheet
    Given the database is purged
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
      | Admin.yml                                                                |
    When I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event"
    Then I should see "admin.event.page.orderFinder.title"
    And I should see "admin.event.page.orderFinder.explanation"
    Then I fill in "form.order_find.children.numero.label" with "test"
    And I press "form.order_find.children.submit.label"
    Then I should be on this page "/admin/fr/event"
    And I should see "validators.order.numeroNotValid"
    Then I fill in "form.order_find.children.numero.label" with "01-01-03"
    And I press "form.order_find.children.submit.label"
    Then I should be on this page "/admin/fr/event/1/sheet/1#sheetOrders"
    And I should see "Onera"
    And I should see "01-01-03"

