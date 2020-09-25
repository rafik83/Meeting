@admin @mail @transaction

Feature: Admin Transaction
  I need to be able to manage the transaction for a participant

  Scenario: I can see a list of transactions
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays-2016.vimeet.proximum"
    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet
    And there is a participant for this sheet and this user
    And there is a pending transaction with reference "ref_2" and amount 150 for this sheet
    And there is a paid transaction with reference "ref_1" and amount 150 for this sheet
    And elastica is populate
    And I am logged as admin
    And I am on this page "/fr/event/1/sheet"
    When I go to this page "/fr/event/1/sheet/1"
    And I should see "user_asddays_1@proximum.com"
    And I should see "admin.sheet.details.transactions"
    And I should see "admin.transaction.state.pending"
    And I should see "admin.transaction.state.paid"

  Scenario: I can add a new transaction
    Given I am logged as admin
    And I am on this page "/fr/event/1/sheet"
    When I go to this page "/fr/event/1/sheet/1"
    And I follow "admin.transaction.add"
    Then I should be on this page "/fr/event/1/sheet/1/transaction/create"
    When I fill in the following:
      | form.create_transaction.children.amount.label    | 25             |
      | form.create_transaction.children.mode.label      | bank_card      |
      | form.create_transaction.children.reference.label | transaction_03 |
    And I check the "form.transaction.children.state.pending" radio
    And I press "form.create_transaction.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1#sheetOrders"
    And I should see "flash.admin.transaction.create.success"

  Scenario: I can edit a transaction
    Given I am logged as admin
    And I am on this page "/fr/event/1/sheet"
    When I go to this page "/fr/event/1/sheet/1"
    And I follow "update_transaction_1_link"
    Then I should be on this page "/fr/event/1/sheet/1/transaction/1/update"
    And I fill in the following:
      | form.update_transaction.children.amount.label | 525 |
    Then I check the "form.transaction.children.state.paid" radio
    And I press "form.update_transaction.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1#sheetOrders"
    And I should see "flash.admin.transaction.update.success"
    And the "transaction.confirm" mail should be sent to "user_asddays_1@proximum.com" from "no-reply@asddays-2016.vimeet.proximum"
    And the "transaction.confirm" mail should be sent in bcc to "team-project@example.net" from "no-reply@asddays-2016.vimeet.proximum"

  Scenario: I can remove a transaction
    Given I am logged as admin
    And I am on this page "/fr/event/1/sheet"
    When I go to this page "/fr/event/1/sheet/1"
    And I press "admin.transaction.remove"
    Then I should be on this page "/fr/event/1/sheet/1"
    And I should see "flash.admin.transaction.remove.success"
