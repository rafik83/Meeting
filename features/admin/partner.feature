@admin @partner

Feature: Partner available features

  Scenario: I can see the sheet participation list
    Given the database is purged
    And the event "Les rendez-vous CARNOT 2016" is created
    And the admin "partner@proximumgroup.com" with role "ROLE_PARTNER" is created
    And this admin can access this event
    And there is a type "Fournisseur" in this event
    And the user "alice@example.com" is created
    And this user is declared in this event
    And the user "bob@example.com" is created
    And this user is declared in this event
    And there is a sheet
    And there is a participant for this sheet and this user
    And I am logged with this admin
    And I go to this page "/fr/event"
    And I should see "Les rendez-vous CARNOT 2016"
    And I should not see "admin.users.link"
    And I should see "admin.sheet.link"
    When I go to this page "/fr/event/1/sheet"
    And I should see "admin.sheet.title"
    # verify that I can check the checkbox
    When I check "sheet_batch_ids_1"
    Then I should see "form.sheet_batch.children.accept.label"
    And I should see "admin.sheet.validate"
    And I should see "admin.sheet.commercialFollowUp"
    And I should not see "form.sheet_batch.children.addCatalog.label"
    And I should not see "form.sheet_batch.children.removeCatalog.label"
    And I should not see "form.sheet_batch.children.enable.label"
    And I should not see "form.sheet_batch.children.disable.label"

  Scenario: I can see sheet detail
    Given I am logged with "partner@proximumgroup.com" on admin
    When I go to "/fr/event/1/sheet"
    Then I go to "/fr/event/1/sheet/1"
    And I should see "admin.sheet.details.dashboard.title"
    And I should see "admin.sheet.details.historic.title"
    And I should not see "admin.sheet.details.orders_and_transactions.title"

  Scenario: Partner can only access to sheet, account and login/logout
    Given I am logged with "partner@proximumgroup.com" on admin
    When I go to "/fr/event/1/sheet"
    Then the response status code should be 200
    When I go to "/fr/event/1/sheet/1"
    Then the response status code should be 200
    When I go to "/fr/account"
    Then the response status code should be 200

  Scenario: Partner can't access other page
    Given I am logged with "partner@proximumgroup.com" on admin
    When I go to "/fr/event/1/practical-info"
    Then the response status code should be 403
    When I go to "/fr/event/1/category"
    Then the response status code should be 403
    When I go to "/fr/event/1/type"
    Then the response status code should be 403
    When I go to "/fr/event/1/order"
    Then the response status code should be 403
    When I go to "/fr/event/1/meeting"
    Then the response status code should be 403
    When I go to "/fr/event/1/users"
    Then the response status code should be 403
