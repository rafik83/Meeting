@admin
@order
@product
@package
Feature: Edit an order
  As an Admin, I need to be able to edit an order, add custom rows, edit them and remove them

  Scenario: Add custom row
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
    And I am on this page "/fr/event/1/order/1/row/add/to-group/5"
    Then I fill in the following:
      | order_row_label    | My awesome reduction |
      | order_row_price    | -500                 |
      | order_row_quantity | 1                    |
    And I press "order_row_submit"
    Then the response status code should be 200
    And I should see "My awesome reduction"
    And I should see "1 566"
    And I should see "-500"

  Scenario: Check new custom row in pro forma
    Given I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I follow "navigation.links.package.order_list"
    Then the response status code should be 200
    When I follow "order.list.pro_forma.link"
    Then the response status code should be 200
    And I should see "1 566"
    And I should see "-500"

  Scenario: Update custom row
    Given I am logged with "test@test.com" on admin
    When I am on this page "/fr/event/1/order/1/edit"
    And I follow "admin.order_edit.edit_custom_row"
    Then the response status code should be 200
    When I fill in the following:
      | order_row_label    | Another awesome reduction |
      | order_row_price    | -200                      |
      | order_row_quantity | 2                         |
    And I press "order_row_submit"
    Then the response status code should be 200
    And I should see "Another awesome reduction"
    And I should see "1 666"
    And I should see "-400"

  Scenario: Check updated custom row in pro forma
    Given I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I follow "navigation.links.package.order_list"
    Then the response status code should be 200
    When I follow "order.list.pro_forma.link"
    Then the response status code should be 200
    And I should see "1 666"
    And I should see "-400"

  Scenario: Remove custom row
    Given I am logged with "test@test.com" on admin
    When I am on this page "/fr/event/1/order/1/edit"
    And I follow "admin.order_edit.remove_custom_row"
    When the response status code should be 200
    Then I should not see "Another awesome reduction"

  Scenario: Check removed custom row in pro forma
    Given I am logged with "user_asddays_2@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    When I go to this page "/fr"
    Then I should be on this page "/fr/sheet/2"
    When I follow "navigation.links.package.order_list"
    Then the response status code should be 200
    When I follow "order.list.pro_forma.link"
    Then the response status code should be 200
    And I should see "2 066"
    And I should not see "-400"
