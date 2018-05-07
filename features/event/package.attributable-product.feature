@event @package @product
Feature: Select participants for attributable product
  I need to be able to buy product with participants to select

  Scenario: I can buy an attributable product
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
    And there is an attributable option called "Gala dinner" with a price of "139"
    And there is an attributable option called "Conference pass" with a quantity max of "1" and a price of "50"
    And these options are assigned to this package
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
    And I should see "Gala dinner"
    And I should see "Conference pass"
    # Gala dinner
    When I select "0" from "options[4][participants][]"
    And I additionally select "1" from "options[4][participants][]"
    # Conference pass
    When I select "0" from "options[5][participants][]"
    And I additionally select "1" from "options[5][participants][]"
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    And I should see "form.options.selectParticipantsQuantityMax.label"
    When I select "0" from "options[5][participants][]"
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/billing-info"
    When I fill my billing informations
    And I press "form.billing_info_update.children.submit.label"
    Then I should be on this page "/fr/sheet/1/package/summary"
    And I should see "Formule premium"
    And I should see "Pass one day"
    And I should see "Gala dinner"
    When I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    And I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"
    When I check the "form.payment_choice.children.paymentMode.bank_check" radio
    And I press "package.payment.pay.label"
    Then I should be on this page "/fr/sheet/1/orders"
