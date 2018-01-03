Tasks list:

- [ ]
- [ ]
- [ ]

Tasks to run after deploying on preprod or prod:

- [ ] Reindex Elasticsearch: `$ bin/console fos:elastica:populate --env=prod --no-debug`
- [ ] Run the whatever calculator : `$ bin/console vimeet:whatever-command`
- [ ] ...

Definition of Done:

- [ ] 1. Check access to controllers (if concerned)
- [ ] 2. Respect the UI Admin (if the feature concerns the Admin)
- [ ] 3. Generate DB migration if the structure changes (`$ make migrations`)
- [ ] 4. Re-check Acceptance test: proofreading the story at the development end
- [ ] 5. Unit tests pass
- [ ] 6. Functional tests pass
- [ ] 7. No errors on Insight
- [ ] 8. No conflict with `master` or solve them as soon as possible.
- [ ] 9. Create translation keys in French and listed alphabetically
- [ ] 10. Copy/Paste all new translations keys in the corresponding user story on Jira
- [ ] 11. To be reviewed (have at least one "approve") in order to pass the feature to "preprod"

*Cross out unrelevant item of the DoD (items 1 to 5 only), example:*
- ~~Generate DB migration if the structure changes~~
