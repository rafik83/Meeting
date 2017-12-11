@event @notification
Feature: Navigate in my notification

  Scenario: I can see my notification list
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
      | @InfrastructureBundle/DataFixtures/ORM/ASDDays2016-Notifications.yml     |
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr"
    When I follow "navigation.links.notification"
    Then I should be on this page "/fr/sheet/1/notification"
    And I should see "notification.list.title.label"

  Scenario: I have receive an sheet uncompleted translation notification
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr/sheet/1/notification"
    Then I should see "notification.sheet.completeTranslation"
    And I should see "notification.label.required"
    And I should see "notification.category.sheet.label"

  Scenario: I have receive a notification for select a package and make an order
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr/sheet/1/notification"
    Then I should see "notification.package.noOrder"
    And I should see "notification.label.required"
    And I should see "notification.category.package.label"

  Scenario: I have receive notifications for warning me with my pending transaction
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr/sheet/1/notification"
    Then I should see "notification.transaction.pending"

  Scenario: I have receive a paid transaction notification
    When I am logged with "user_asddays_1@proximum.com" on event "http://asddays-2016.vimeet.proximum"
    And I go to this page "/fr/sheet/1/notification"
    Then I should see "notification.transaction.paid"
