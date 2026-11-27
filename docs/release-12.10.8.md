# u5CMS 12.10.8

Versioned form edits are now serialised within each installation database. Two edits arriving at the same time could previously both move the active row into history and then both insert a replacement. In a reproduced race, both requests returned successfully while two active versions remained for the same response.

The correction covers edits from a version-checked front-end form and the backend edit and delete handlers. It uses a short database advisory lock only around the existing versioning sequence. Ordinary append-only submissions remain independent, the form-data schema does not change and no configuration change is required.

## Verification

The fault was reproduced with two independent PHP workers against disposable MariaDB databases on the stable release, its direct predecessor and V13–V17. The corrected stable source completed 200 pairs of simultaneous edits with exactly one active row and all 400 edits retained in the version history. The database-lock regression checks contention, release and immediate reuse. The cumulative form comparison also ran 18 delivered handler scenarios on v12.9.5, the direct predecessor, 12.10.7, V13–V17 and the corrected working tree without changing their expected results.
