@admin @admin-event

Feature: See, create and update event
  I need to be able to see, create and update an event

  Scenario: See event
    Given the database is purged
    And the event "Les rendez-vous CARNOT 2016" is created
    And the super admin "test@test.com" is created
    And I am logged with this admin
    When I go to this page "/en/event"
    Then I should see "Les rendez-vous CARNOT 2016"

  Scenario: create event
    Given I am logged with "test@test.com" on admin
    And I am on this page "/en/event"
    And I should see "admin.event.create.title"
    Then I follow "admin.event.create.title"
    Then the response status code should be 200
    And I should be on this page "/en/event/create"
    And I check "form.event_create.children.visible.label"
    And I fill in the following:
      | form.event_create.children.title.label           | Alternative Event                 |
      | form.event_create.children.domain.label          | alternative-event.vimeet.proximum |
      | form.event_create.children.vat.label             | 20                                |
      | form.event_create.children.organiserName.label   | Proximum                          |
      | form.event_create.children.emailTeam.label       | team-project@example.net          |
    And I select "Europe/Paris" from "form.event_create.children.timeZone.label"
    And I select "fr" from "form.event_create.children.fallback.label"
    And I select "fr" from "form.event_create.children.locales.label"
    And I check the "form.vatMode.ati" radio
    And I select "FR" from "form.event_create.children.country.label"
    And I select "EUR" from "form.event_create.children.currency.label"
    And I press "form.event_create.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.event.create.success"
    And I should be on this page "/en/event"
    And I should see "Alternative Event"
    And I should not see "Invisible"

  Scenario: update event
    Given I am logged with "test@test.com" on admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.update.link"
    Then the response status code should be 200
    And I should be on this page "/en/event/1/update"
    And I fill in the following:
      | event_update_title                       | Other event                                                                    |
      | event_update_translations_fr_description | LES RENDEZ-VOUS DE LA R&D POUR LES ENTREPRISE                                  |
      | event_update_translations_en_description | In 7 editions, les Rendez-vous CARNOT became the major R&D event for innotion. |
      | event_update_emailTeam                   | team-event@example.net                                                         |
      | event_update_analyticsCode               | analyticsCode                                                                  |
      | event_update_domain                      | rdv-carnot-2016.vimeet.proximum                                                |
    And I select "fr" from "event_update_fallback"
    And I select "EUR" from "event_update_currency"
    And I press "form.event_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.event.update.success"
    And the "event_update_emailTeam" field should contain "team-event@example.net"
    And the "event_update_analyticsCode" field should contain "analyticsCode"
    When I go to "/fr/event"
    Then I should see "Other event"
    When I go to "http://rdv-carnot-2016.vimeet.proximum/fr"
    Then the response status code should be 200
    Then I go to "http://rdv-carnot-2016.vimeet.proximum/en"
    And the response status code should be 200

  Scenario: update invoice prefix on event
    Given the invoice prefix with name "ViMeet" and prefix "Vi" is created and is default
    And the invoice prefix with name "RdvCarnot" and prefix "RdvCarnot" is created
    And I am logged with "test@test.com" on admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.update.link"
    Then the response status code should be 200
    And I should be on this page "/en/event/1/update"
    And I select "RdvCarnot" from "event_update_invoicePrefix"
    And I press "form.event_update.children.submit.label"
    Then the response status code should be 200
    And I should see "flash.admin.event.update.success"
    And the "event_update_invoicePrefix" field should contain "0"

  Scenario: I should not access to an invisible event
    Given I am logged with "test@test.com" on admin
    And I am on this page "/en/event/1"
    And I go to this page "/en/event/1/update"
    When I uncheck "form.event_update.children.visible.label"
    And I press "form.event_update.children.submit.label"
    Then I go to this page "/en/event"
    And I should see "Invisible"
    Then this event page "http://super-event.vimeet.proximum/fr" returns 404

  Scenario: I can duplicate an event
    Given I am logged with "test@test.com" on admin
    And the event "Paris Space Week" is created
    And I am on this page "/en/event"
    When I follow "admin.event.duplicate.title"
    Then I should be on this page "/en/event/duplicate"
    And I should see "Paris Space Week"
    And I check the "Paris Space Week" radio
    When I press "form.event_duplicate.children.submit.label"
    Then I should be on this page "/en/event/create/from/3"
    And the "event_create[title]" field should contain "Paris Space Week"
