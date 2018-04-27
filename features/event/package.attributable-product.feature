@event @package @product
Feature: Select participants for attributable product
  I need to be able to buy product with participants to select

  Scenario: I can buy a attributable product
    Given the database is purged
    And the event "Super Event" is created
    And there is a type in this event
    And there is a package "Wonderful Package" for this event
    And this package is assigned to this type
    And there is a plan called "Formule premium" with a price of "99"
    And this plan is assigned to this package
    And there is a product Participant called "Pass one day" with a price of "199" and a max quantity of 10
    And this product participant is assigned to this package
    And this plan includes this product participant 1 times
    And there is a planning called "Planning meetings" with a price of "39"
    And this product planning is assigned to this package
    And there is a sheet for this type with the title "Star Fleet"
    And the user "kirk@example.net" is created
    And there is a participant for this sheet and this user
    And the user "spock@example.net" is created
    And there is a participant for this sheet and this user
    When I am logged with "kirk@example.net" on event "http://super-event.vimeet.proximum"
    And I go to this page "/fr/sheet"
    Then I should be on this page "/fr/sheet/1"
    When I go to this page "/fr/sheet/1/package/step/1"
    Then I should see "Formule premium"
    When I select "0" from "plans[plan]"
    And I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "Pass one day"
    When I select "Pass one day" from "participant_and_planning_1"
    And I select "Pass one day" from "participant_and_planning_2"
    And I fill in "participant_and_planning[planningQuantity][quantity]" with "2"
    And I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    When I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/billing-info"
    When I check the "gender.man" radio
    And I fill in the following:
      | form.billing_info_update.children.lastname.label  | Jean         |
      | form.billing_info_update.children.firstname.label | Test         |
      | form.billing_info_update.children.function.label  | DG           |
      | form.billing_info_update.children.phone.label     | +33456789    |
      | form.billing_info_update.children.mobile.label    | +33456789    |
      | form.billing_info_update.children.email.label     | jean@test.fr |
      | form.billing_info_update.children.company.label   | ELAO-TEST    |
      | form.billing_info_update.children.street.label    | 10 Rue test  |
      | form.billing_info_update.children.zipcode.label   | 75002        |
      | form.billing_info_update.children.city.label      | PARIS        |
      | form.billing_info_update.children.country.label   | FR           |
      | form.billing_info_update.children.vatNumber.label | 123456789    |
    And I press "form.billing_info_update.children.submit.label"
    Then I should be on this page "/fr/sheet/1/package/summary"
    And I should see "Formule premium"
    And I should see "Pass one day"
    When I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    And I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"
    When I check the "form.payment_choice.children.paymentMode.bank_check" radio
    And I press "package.payment.pay.label"
    Then I should be on this page "/fr/sheet/1/orders"
