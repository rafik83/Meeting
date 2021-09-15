@admin

Feature: Update spanish event
  I need to be able to update spanish event configuration

  Scenario: I can see the event
    Given the database is purged
    And the event "Evento en español" is created
    And the locale for this event is "es"
    And the domain for this event is "spanish-event.vimeet.proximum"
    And there is a type "Exponente" in this event
    And there is a sheet for this type with the title "Desigual"
    And there is a type "Inversor" in this event
    And I am logged as admin
    When I go to this page "/en/event"
    Then I should see "Evento en español"

  Scenario: I can see the event on front
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.see_on_front.link"
    Then I should be on this url "http://spanish-event.vimeet.proximum/es/"

  Scenario: I can see the update event form
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.event.update.link"
    Then I should be on this page "/en/event/1/update"
    When fill in the following:
      | event_update_translations_fr_description | Evenement espagnol |
      | event_update_translations_en_description | Spanish event      |
    And I press "form.event_update.children.submit.label"
    Then I should see "flash.admin.event.update.success"

  Scenario: I can see the who see who list
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.rule.link"
    Then I should be on this page "/en/event/1/who-see-who"

  Scenario: I can add a who see who rule
    Given I am logged as admin
    And I am on this page "/en/event/1/who-see-who"
    When I fill in the following:
      | who_see_who_seer    | type:1 |
      | who_see_who_seeable | type:2 |
      | priority            | 2      |
    And I press "form.who_see_who.children.submit.label"
    Then I should be on this page "/en/event/1/who-see-who/see-what/1"
    And I should see "Who.see_who_but_dont_see"
    And I should see "form.rule_see_what.children.seeWhat.label"
    Then I select "sheet_title" from "form.rule_see_what.children.seeWhat.label"
    And I additionally select "participant_firstname" from "form.rule_see_what.children.seeWhat.label"
    And I press "form.rule_see_what.children.submit.label"
    Then I should see "flash.admin.event.who_see_what.success"

  Scenario: I can see types list
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.type.link"
    Then I should be on this page "/en/event/1/type"
    And I should see "Exponente"
    And I should see "Inversor"

  Scenario: I can see categories list
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.category.link"
    Then I should be on this page "/en/event/1/category"

  Scenario: I can add a category
    Given I am logged as admin
    And I am on this page "/en/event/1/category"
    When I follow "admin.category.add"
    Then I should be on this page "/en/event/1/category/create"
    When I fill in the following:
      | category_create[translations][es][title] | Some label |
    And I check "Exponente"
    And I press "form.category_create.children.submit.label"
    Then I should be on this page "/en/event/1/category"
    And I should see "flash.admin.category.create.success"

  Scenario: I can see meeting requests list
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.meeting_request.link"
    Then I should be on this page "/en/event/1/meeting-request"

  Scenario: I can see meeting list
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.meeting.link"
    Then I should be on this page "/en/event/1/meeting"

  Scenario: I can see sheet list
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.sheet.link"
    Then I should be on this page "/en/event/1/sheet"

  Scenario: I can see happening list
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.happening.link"
    Then I should be on this page "/en/event/1/happening"

  Scenario: I can see happening speaker list
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.happening_speaker.link"
    Then I should be on this page "/en/event/1/happening/speaker"

  Scenario: I can add a happening speaker
    Given I am logged as admin
    And I am on this page "/en/event/1/happening/speaker"
    And I follow "admin.happening_speaker.add"
    And I should be on this page "/en/event/1/happening/speaker/create"
    When I fill in the following:
      | create_speaker[firstname]                  | Henry     |
      | create_speaker[lastname]                   | Dupont    |
      | create_speaker[translations][es][position] | Developer |
      | create_speaker[organization]               | Elao      |
    And I press "form.create_speaker.children.submit.label"
    Then I should see "flash.admin.speaker.create.success"

  Scenario: I can see happening categories lis
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.happening_category.link"
    Then I should be on this page "/en/event/1/happening/category"

  Scenario: I can add a happening category
    Given I am logged as admin
    And I am on this page "/en/event/1/happening/category"
    And I follow "admin.happening_category.add"
    And I should be on this page "/en/event/1/happening/category/create"
    When I fill in the following:
      | category_create[rank]                    | 1             |
      | category_create[picto]                   | Cafe_1        |
      | category_create[translations][es][title] | CategoryLabel |
    And I press "form.category_create.children.submit.label"
    Then I should see "flash.admin.happening.category.create.success"

  Scenario: I can add a happening
    Given I am logged as admin
    And I am on this page "/en/event/1/happening"
    And I follow "admin.happening.add"
    And I should be on this page "/en/event/1/happening/create"
    When I select "CategoryLabel" from "happening_create[category]"
    And I fill in the following:
      | happening_create[translations][es][title]       | HappeningTitle       |
      | happening_create[translations][es][description] | HappeningDescription |
      | happening_create[begin]                         | 09/09/2016 10:10     |
      | happening_create[end]                           | 09/09/2016 12:10     |
      | happening_create[questionAllowed]               | 1                    |
      | happening_create[limitParticipant]              | 15                   |
    And I press "form.happening_create.children.submit.label"
    Then I should see "flash.admin.happening.create.success"

  Scenario: I can see spot list
    Given I am logged as admin
    And I am on this page "/en/event/1"
    When I follow "admin.spot.link"
    Then I should be on this page "/en/event/1/spot"
