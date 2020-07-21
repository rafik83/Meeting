@admin
@order
@invoice
Feature: Find a sheet with an order numero or an invoice numero
  As an Admin, I need to be able to find a sheet with the order or invoice numero I have

  Scenario: Find the sheet via an order numero
    Given the database is purged
    And the event "Best of web" is created
    And there is a sheet with the title "URSCOP"
    And there is billing info for this sheet
    And there is an order with the amount of 155 for this sheet
    And I am logged as admin
    When I am on this page "/fr/event/order-invoice/find"
    Then I should see "admin.event.page.orderInvoiceFinder.title"
    And I should see "admin.event.page.orderInvoiceFinder.explanation"
    When I fill in "form.order_invoice_find.children.numero.label" with "test"
    And I check the "form.order_invoice_find.children.type.choices.order" radio
    And I press "form.order_invoice_find.children.submit.label"
    Then I should be on this page "/fr/event/order-invoice/find"
    And I should see "validators.order.numeroNotValid"
    When I fill in "form.order_invoice_find.children.numero.label" with "01-01-01"
    And I press "form.order_invoice_find.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1#sheetOrders"
    And I should see "URSCOP"
    And I should see "01-01-01"

  Scenario: Find the sheet via an invoice numero
    Given the database is purged
    And the event "Best of web" is created
    And there is a sheet
    And there is an invoice with the numero "Vi2017-0001" for this sheet
    And I am logged as admin
    When I am on this page "/fr/event/order-invoice/find"
    Then I should see "admin.event.page.orderInvoiceFinder.title"
    And I should see "admin.event.page.orderInvoiceFinder.explanation"
    When I fill in "form.order_invoice_find.children.numero.label" with "test"
    And I check the "form.order_invoice_find.children.type.choices.invoice" radio
    And I press "form.order_invoice_find.children.submit.label"
    Then I should be on this page "/fr/event/order-invoice/find"
    And I should see "validators.invoice.numeroNotValid"
    When I fill in "form.order_invoice_find.children.numero.label" with "Vi2017-0001"
    And I check the "form.order_invoice_find.children.type.choices.invoice" radio
    And I press "form.order_invoice_find.children.submit.label"
    Then I should be on this page "/fr/event/1/sheet/1#sheetOrders"
