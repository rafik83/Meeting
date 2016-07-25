@admin

Feature: Edit an order
  As an Admin, I need to be able to edit an order, add custom rows, edit them and remove them

  Scenario: Add custom row
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
      | Admin.yml                                                                |
    When I am logged with "test@test.com" on admin
    And I am on this page "/admin/fr/event/1/order/1/edit"
    Then the response status code should be 200
    When I am on this page "/admin/fr/event/1/order/1/row/add/to-group/5"
    And the response status code should be 200
    Then I fill in the following:
      | order_row_label    | My awesome reduction |
      | order_row_price    | -500                 |
      | order_row_quantity | 1                    |
    And I press "order_row_submit"
    Then the response status code should be 200
    And I should see "My awesome reduction"
    And I should see "1 389"

  Scenario: Update custom row
    And I am on this page "/admin/fr/event/1/order/1/row/2/update"
    Then the response status code should be 200
    When I fill in the following:
      | order_row_label    | Another awesome reduction |
      | order_row_price    | -200                 |
      | order_row_quantity | 2                    |
    And I press "order_row_submit"
    Then the response status code should be 200
    And I should see "Another awesome reduction"
    And I should see "1 489"

  Scenario: Remove custom row
    When I am on this page "/admin/fr/event/1/order/1/row/1/update"
    Then the response status code should be 200
    And I should not see "Another awesome reduction"
