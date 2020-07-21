@event @notification
Feature: Navigate in my notification

  Scenario: I can see my notification list
    Given the database is purged
    And the event "Super event" is created
    And there is a type in this event
    And there is a package "Wonderful Package" for this event
    And this package is assigned to this type
    And there is a plan called "Formule premium" with a price of "99"
    And this plan is assigned to this package
    And there is a sheet for this type with the title "Proximum"
    And there is billing info for this sheet
    And the user "user_asddays_1@proximum.com" is created
    And there is a participant for this sheet and this user
    And the completeness of this sheet for the locale "fr" is 80
    And there is an order with the amount of 300 for this sheet
    And there is a paid transaction with reference "ref_1" and amount 150 for this sheet
    And there is a pending transaction with reference "ref_2" and amount 150 for this sheet
    And there is a package selected notification for this sheet
    And there is a sheet translation completeness notification for this sheet
    And there is a pending transaction notification for this sheet
    When I am logged with "user_asddays_1@proximum.com" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr"
    When I follow "navigation.links.notification"
    Then I should be on this page "/fr/sheet/1/notification"
    And I should see "notification.list.title.label"

  Scenario: I have receive an sheet uncompleted translation notification
    When I am logged with "user_asddays_1@proximum.com" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr/sheet/1/notification"
    Then I should see "notification.sheet.completeTranslation"
    And I should see "notification.label.required"
    And I should see "notification.category.sheet.label"

  Scenario: I have receive a notification for select a package and make an order
    When I am logged with "user_asddays_1@proximum.com" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr/sheet/1/notification"
    Then I should see "notification.package.noOrder"
    And I should see "notification.label.required"
    And I should see "notification.category.package.label"

  Scenario: I have receive notifications for warning me with my pending transaction
    When I am logged with "user_asddays_1@proximum.com" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr/sheet/1/notification"
    Then I should see "notification.transaction.pending"

  Scenario: I have receive a paid transaction notification
    When I am logged with "user_asddays_1@proximum.com" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr/sheet/1/notification"
    Then I should see "notification.transaction.paid"
