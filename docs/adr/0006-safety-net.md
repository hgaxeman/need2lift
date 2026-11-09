---
status: accepted
---

# Freeze behaviour with HTTP golden files, and run static analysis and Rector as checks

Before any code moves:

- Golden files at the HTTP boundary record status, content type and body for a hello request, an unknown request, and a dispatch failure. Only volatile values are normalised, and these replies have none today. Golden files change only with approval ([ADR-0002](0002-frozen-contracts.md)).
- No transport doubles are added. The inventory on 2026-11-09 found no mail, outbound HTTP, SOAP, FTP, mailbox, database or application log sink. The suite must pass with network egress blocked.
- PHPStan runs at its maximum level, with zero errors and no baseline. It is run by hand, since there is no CI ([ADR-0013](0013-demo-no-production-no-ci.md)).
- A committed `rector.php` pins the PHP version the code actually runs on. The Rector version is pinned (2.8.4 was installed on 2026-11-09), and a dry run that produces any diff fails the check when run by hand.
