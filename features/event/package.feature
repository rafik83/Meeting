@event @package @product @mail
Feature: Complete my package
  I need to be able to buy plan, participants, planning and options

  Scenario: I can buy plan
    Given the database is purged
    And the event "ASD Days" is created
    And the domain for this event is "asddays-2016.vimeet.proximum"

    And there is a type "Fournisseur" in this event

    # package with product of type "Participant"
    And there is a package "Pack participants+rdv" for this event
    And there is a product Participant called "Participant Supplémentaire" with a price of "130" and a max quantity of 10
    And this product participant is assigned to this package
    And there is a promotion "Assdays Promotion Code" with code "ASDDAYS10" for this product participant
    And this package is assigned to this type
    And there is a plan called "Formule premium" with a price of "99"
    And this plan is assigned to this package
    And this plan includes this product participant 1 times

    # package with product of type "Planning"
    And there is a planning called "Planning meetings" with a price of "39"
    And this product planning is assigned to this package
    And there is an option subject to validation called "Gala dinner" with a price of "139"
    And there is a promotion "Assdays Promotion Code 2" with code "ASDDAYS20" for this product options
    And there is an option called "Goodies" with a price of "5" and a max quantity of 3
    And there is an option called "Conference pass" no more available
    And there is an option called "Gold sponsor" out of stock
    And these options are assigned to this package

    And the user "user_asddays_1@proximum.com" is created
    And there is a sheet for this type with the title "Aanera"
    And there is a participant for this sheet and this user
    And there is a paid transaction with reference "ref_1" and amount 100 for this sheet

    And I am logged with "user_asddays_1@proximum.com" on front

    When I am on this page "/fr"
    And I go to this page "/fr/sheet"
    And I follow "navigation.category.package"
    Then I should be on this page "/fr/sheet/1/package/step/1"
    And I should see "Formule premium"
    When I select "0" from "plans[plan]"
    And I press "package.plans.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    When I go to this page "/fr/sheet/1/package/step/1"
    Then the radio "plans_plan_2" should be checked

  Scenario: I can buy participant product
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr/sheet/1/package/step/2"
    Then I should see "Participant supplémentaire"
    And I should see "package.product.unitPrice"
    When I fill in "participant_and_planning[planningQuantity][quantity]" with "1"
    And I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"

  Scenario: I can buy planning
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr/sheet/1/package/step/2"
    Then I should see "Planning meetings"
    And I should see "package.participant_planning.validate"
    And I should see "package.product.unitPrice"
    And I should see "package.product.totalPrice"
    When I fill in "participant_and_planning[planningQuantity][quantity]" with "1"
    And I press "package.participant_planning.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"

  Scenario: I can buy options
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr/sheet/1/package/step/3"
    Then I should see "Gala dinner"
    And I should see "Conference pass"
    And I should see "package.product.subjectedToValidationHelp"
    When I fill in the following:
      | options[4][quantity] | 1  |
      | options[5][quantity] | 10 |
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/step/3"
    And I should see "package.product.quantityNotMatch"
    And I should see "package.product.unavailable"
    And I should see "package.product.outOfStock"
    When I fill in the following:
      | options[5][quantity] | 3 |
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/billing-info"
    When I go to this page "/fr/sheet/1/package/step/3"
    Then the "options[4][quantity]" field should contain "1"
    Then the "options[5][quantity]" field should contain "3"

  Scenario: I can add a participant at step 2
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And I am on this page "/fr/sheet/1/package/step/2"
    When I follow "package.participant.add"
    Then I should see "sheet.participant.sendInvite"
    And I fill in the following:
      | add_participant_firstName | Truc         |
      | add_participant_lastName  | Test         |
      | add_participant_email     | truc@test.fr |
    When I press "sheet.participant.sendInvite"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "Truc TEST"
    And I should see "TT"

  Scenario: I can fill my billing-info
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr/sheet/1/package/step/3"
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/billing-info"
    And I check the "gender.man" radio
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

  Scenario: I can see my package summary
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr/sheet/1/package/summary"
    Then I should see "package.summary.title"
    And I should see "Formule premium"
    And I should see "Participant supplémentaire"
    And I should see "Planning meetings"
    And I should see "Groupe 1"
    And I should see "Gala dinner"
    And I should see "Goodies"
    And I should see "package.summary.pay"
    Then I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    And I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"

  Scenario: I can add multiple promotion code
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr/sheet/1/package/summary"
    Then I should see "package.summary.title"
    And I fill in "package_summary_promotion_code_promotionCode" with "ASDDAYS10"
    And I press "package.summary.promotion.button.label"
    Then I should be on this page "/fr/sheet/1/package/summary#summary-promo-code-row"
    And I should see "Assdays Promotion Code"
    Then I fill in "package_summary_promotion_code_promotionCode" with "ASDDAYS20"
    And I press "package.summary.promotion.button.label"
    Then I should be on this page "/fr/sheet/1/package/summary#summary-promo-code-row"
    And I should see "Assdays Promotion Code"
    And I should see "Assdays Promotion Code 2"

  Scenario: I can choose my payment method
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr/sheet/1/package/summary"
    Then I check "form.package_summary_terms_of_sale.children.termsOfSale.label"
    And I press "package.summary.pay"
    Then I should be on this page "/fr/sheet/1/package/payment"
    And I should see "package.payment.total.toPay"
    And I check the "form.payment_choice.children.paymentMode.bank_check" radio
    When I press "package.payment.pay.label"
    Then I should be on this page "/fr/sheet/1/orders"
    And I should see "order.transaction.state.pending"
    And the "order.confirm" mail should be sent to "user_asddays_1@proximum.com" from "no-reply@asddays-2016.vimeet.proximum"
    And the "order.confirm" mail should be sent in bcc to "team-project@example.net" from "no-reply@asddays-2016.vimeet.proximum"

  Scenario: I can see my package total summary:
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And I am on this page "/fr/sheet"
    When I follow "navigation.links.package.order_summary_total"
    Then I should be on this page "/fr/sheet/1/order/summary"
    And I should see "package.summary.title"
    And I should see "package.summary.promotion.label"
    And I should see "summary.total"
    And I should see "package.summary.totalVat"
    And I should see "summary.totalToPay"

  Scenario: I can see how to pay in my transaction list
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr/sheet"
    And I follow "navigation.links.package.order_list"
    Then I should be on this page "/fr/sheet/1/orders"
    And I should see "order.list.title"
    And I should see "order.transaction.list.title"
    And I should see "order.transaction.howtopay"
    When I follow "order.transaction.howtopay"
    Then I should be on this page "/fr/sheet/1/payment"
    And I should see "package.payment.billingAddress"
    And I should see "package.payment.bankInfo"

  Scenario: I can see the remaining amount to pay
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    And I am on this page "/fr/sheet"
    When I follow "navigation.links.package.order_list"
    Then I should be on this page "/fr/sheet/1/orders"
    # Order total
    And I should see "482,40 €"
    # Remaining to pay
    And I should see "382,40 €"
    And I should see "order.list.remainingToPay"
    And I should see "order.list.pay_remaining"

  Scenario: I can not delete option assigned to previously used promotion code
    Given there is an event with domain "asddays-2016.vimeet.proximum"
    And I am logged with "user_asddays_1@proximum.com" on front
    When I am on this page "/fr/sheet/1/package/step/2"
    Then I should see "Gala dinner"
    And the "options[4][quantity]" field should contain "1"
    When I fill in the following:
      | options[4][quantity] | 0 |
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/step/2"
    And I should see "package.product.quantityMinPromotionCode"
    When I fill in the following:
      | options[4][quantity] | 2 |
    And I press "package.product.validate"
    Then I should be on this page "/fr/sheet/1/package/summary"
    And I should see "Gala dinner"
