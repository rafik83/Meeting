Tasks list:

- [ ]
- [ ]
- [ ]

Tasks to run after deploying on preprod or prod:
- [ ] Reindex Elasticsearch: `$ bin/console fos:elastica:populate --env=prod --no-debug`
- [ ] Run the whatever calculator : `$ bin/console vimeet:whatever-command`
- [ ] ...

Definition of Done:

- [ ] 1. Create translation keys in French and listed alphabetically
- [ ] 2. Check access to controllers (if concerned)
- [ ] 3. Respect the UI Admin (if the feature concerns the Admin)
- [ ] 4. Generate DB migration if the structure changes (`$ make migrations`)
- [ ] 5. Re-check Acceptance test: proofreading the story at the development end
- [ ] 6. Unit tests pass
- [ ] 7. Functionnal tests pass
- [ ] 8. No errors on Insight
- [ ] 9. No conflict with `master` or solve them as soon as possible.
- [ ] 10. To be reviewed (have at least one "approve") in order to pass the feature to "preprod"
- [ ] 11. Copy/Paste all new translations keys in the corresponding user story on Jira

*Cross out unrelevant item of the DoD (items 1 to 5 only), example:*
- ~~Generate DB migration if the structure changes~~
