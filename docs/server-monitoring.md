# Server monitoring for many or high-traffic u5CMS installations

[Documentation home](README.md)

## Summary

Monitor shared host capacity and every relevant website and PHP-FPM pool, with an independent external availability check and heartbeat. Choose a deployment variant, configure sustained-breach alerts and verify actual notifications before relying on the service. The ready-to-use prompts let ChatGPT assess and implement the design with authorised access; an operating-system service or monitoring platform performs the continuing checks. Thresholds are starting proposals to validate against the real server.

A server hosting many u5CMS websites needs an early warning when its shared capacity is running out. One slow application, an external dependency or an overlapping background task can occupy PHP workers until unrelated websites also become slow. Monitor the whole hosting server, retain a view of each website, and provide an independent check from outside the server.

This guide explains an operational design and provides copy-and-paste prompts with which ChatGPT can assess, implement and verify it. It does **not** install monitoring or change a u5CMS setting. The thresholds below are proposed starting values, not u5CMS defaults or measured capacity guarantees.

The detailed examples assume Linux, Apache and PHP-FPM, optionally behind nginx and managed by Plesk. Apply the same principles to other hosting arrangements, using their native service and capacity metrics. Monitoring is an infrastructure task and does not require a new u5CMS release. Check the actual PHP, web server and hosting-panel versions before using particular status interfaces.

## Read by task

| Task | Section |
| --- | --- |
| Choose a practical solution and understand costs | [Deployment variants](#deployment-variants) |
| Decide what should trigger a warning | [Measurements and starting thresholds](#measurements-and-starting-thresholds) |
| Understand the installation work | [Implementation sequence](#implementation-sequence) |
| Have ChatGPT do the work | [Ready-to-use ChatGPT prompts](#ready-to-use-chatgpt-prompts) |
| Check that monitoring really works | [Acceptance and routine operation](#acceptance-and-routine-operation) |

## The failure pattern to detect

An HTTP timeout is a late symptom. A typical sequence is:

1. Some requests stop completing promptly: for example, they wait for a database lock, a remote service or a file operation.
2. New traffic and scheduled tasks continue to arrive. More PHP workers remain occupied, and the pool develops a queue.
3. With an Apache-to-FPM request path, waiting PHP requests can also occupy shared Apache capacity. Other websites on that server become affected.
4. Gateways start returning errors, and eventually the administration interface or local mail delivery may also become difficult to use.

This is an example, not a diagnosis of every slow server. Establish the request path and collect evidence before attributing a fault. High CPU utilisation is only one possible signal; waiting work can consume request slots without keeping every CPU busy.

For illustration, a pool limited to 30 workers is already losing useful reserve at 21 occupied workers and has very little reserve at 27. Those numbers say nothing about the server's other pools or its shared Apache limit. Both levels need observation.

## What runs the monitoring?

ChatGPT can install and configure the machinery when given suitable tools and authorised administrative access. The continuing watcher is an operating-system service or a monitoring platform. It must keep working after the conversation ends and after the administrator closes the browser.

| Component | Responsibility |
| --- | --- |
| Local collector and checker | Read host, web server, PHP-pool and database signals with bounded execution time. |
| Alerting service | Apply thresholds, suppress duplicate messages, send warnings and recovery notifications, and report missing measurements. |
| Incident recorder | Save a small, protected diagnostic snapshot when a new incident starts. |
| Independent external monitor | Check selected websites and detect a missing heartbeat from the local checker, including when the hosting node is unavailable. |
| Named operator or operations team | Receive alerts, maintain the configuration and follow the incident runbook. |

A dashboard is useful for investigation, but drawing a graph does not establish that an alert rule is enabled or that its message reaches the operator. Check both. A dashboard's browser refresh interval is also different from the collector's sampling interval and the alert evaluator's interval.

## Deployment variants

### A. Plesk Monitoring with supplementary capacity checks

This is a practical starting point for one Plesk server. Retain the built-in Monitoring/Grafana graphs and review their thresholds and notification recipients. Add a checker for shared Apache capacity and **all** relevant PHP-FPM pools, because the built-in resource graphs do not by themselves establish those checks. [Plesk Monitoring documentation](https://docs.plesk.com/en-US/obsidian/administrator-guide/statistics-and-monitoring/monitoring.68886/).

Plesk's free **Watchdog** extension uses Monit. Its documented default service polling interval is five minutes, and it can restart failed services automatically. That availability-oriented behaviour is different from an early capacity warning. Audit its effective rules, interval and restart actions before enabling it. Do not assume that installing Watchdog supplies the capacity checks described here. [Plesk Watchdog documentation](https://docs.plesk.com/en-US/obsidian/administrator-guide/server-administration/watchdog-system-monitoring-component.70443/).

If a Plesk-managed Monit instance already exists, identify its ownership and supported extension points. Avoid a second instance competing over the same configuration, PID file, port or restart policy. Put supplementary checks under an explicit maintenance arrangement that survives Plesk regeneration and updates.

### B. A dedicated local checker with Monit or systemd

Use this on an unmanaged Linux server, or where the panel's monitoring cannot express the required rules. Implement a small checker outside the website document roots. Run it through one well-defined supervisor: Monit can evaluate a program's exit status and trigger an alert; alternatively, a systemd service/timer can execute the checker and its notification logic. [Monit manual](https://www.mmonit.com/monit/documentation/monit.html).

The checker needs explicit state for sustained breaches, recovery and alert suppression. Give every collection operation a deadline, prevent overlapping runs, and impose an overall runtime budget. A timer or Monit rule alone does not provide a complete implementation of every requirement in this guide. Verify the actual interval and notification delay, including any extra supervisory evaluation cycle.

For a node with many pools, avoid a long series of per-pool timeouts. Use bounded parallelism or a suitable exporter, with a node-wide collection deadline. Record failures per pool so one unavailable endpoint does not suppress the other measurements.

### C. Central monitoring for several servers

For a larger estate, a separate Prometheus/Alertmanager installation with suitable host, Apache, PHP-FPM and database collectors offers central rules and history. Grafana can provide dashboards. Alertmanager supports grouping, deduplication and inhibition, which help prevent one failed hosting node from generating a separate notification for every website. [Alertmanager documentation](https://prometheus.io/docs/alerting/latest/alertmanager/).

Check each collector's maintenance status and its compatibility with the installed services. Keep the central monitor outside the hosting node's failure boundary and restrict access to metrics. A central system hosted only on the monitored node would still share that node's outage.

### Access and cost

| Arrangement | Access required | Software and other costs |
| --- | --- | --- |
| Existing Plesk Monitoring | Suitable Plesk administration rights | Built-in Monitoring is free; configuration and maintenance still take time. |
| Plesk Watchdog or standalone Monit | Panel installation rights and/or root/sudo for service configuration | Watchdog is free; Monit is free and open source. |
| Central monitoring | Access to the monitoring host and authorised metric endpoints | Self-hosting requires compute, storage, maintenance and an alert-delivery route; evaluate the selected software and hosting terms. |
| External website/heartbeat service | An account or an independent machine | Availability, intervals, notification methods and retention depend on the provider and plan. Check current terms before choosing. |
| Shared hosting without administrative access | Website access only | An external check can report an outage; the provider must supply internal capacity monitoring or expose suitable metrics. |

No paid monitoring subscription is inherently required for the local design. Free software does not remove hosting or operational costs. Monit and the separately licensed M/Monit management product are different offerings. See the [Monit project](https://www.mmonit.com/monit/) and the current Plesk documentation for their terms. Do not equate a trial with an ongoing free service.

## Measurements and starting thresholds

Inventory **every** relevant Apache instance, PHP version, FPM master and pool, including staging and seldom-used sites. Track shared services once per node and attribute PHP pressure to its pool and website. Add discovery for newly created or removed pools; a newly hosted site must not silently remain outside monitoring.

The following is a suggested initial policy for an interactive website estate. Sample local capacity about every 15 seconds, then tune thresholds against several days of normal traffic, backups and scheduled work. Record the chosen values and the operator who owns them.

| Signal | Proposed warning | Proposed critical condition |
| --- | --- | --- |
| Shared Apache utilisation | Busy workers at or above 70% of the effective `MaxRequestWorkers` for 2 minutes | At or above 90% for 30 seconds |
| Each PHP-FPM pool | Active workers at or above 70% of its configured `pm.max_children` for 1 minute | At or above 90%, or a non-empty listen queue, for 30 seconds |
| New limit-exhaustion evidence | A new FPM `max_children` log event or a newly observed rise in a supported limit counter | Apache `MaxRequestWorkers` exhaustion, or recurring pool exhaustion with sustained queuing |
| Available host memory | Below 15% for 2 minutes | Below 5% for 30 seconds, or an out-of-memory event |
| CPU, I/O and swap activity | Sustained abnormal utilisation or waiting compared with the node's baseline | Severe sustained pressure accompanied by queues or slow responses; set hardware-specific values |
| Disk space and inodes | At or above 80% used on a relevant filesystem | At or above 90%, or a forecast showing exhaustion before the operator can respond |
| Database | Connections, lock waits or slow-query rates depart persistently from the baseline | Connection exhaustion, persistent blocking or failure of a bounded read-only check |
| External dynamic request, every minute | Two consecutive responses slower than 5 seconds | Two consecutive failed checks, each with a 10-second overall deadline, or unexpected status/content |
| Monitoring freshness | A local metric is stale for two expected samples | Local collection repeatedly fails, or the external receiver misses the agreed heartbeat deadline |

Use memory available to applications as well as service memory trends. Linux filesystem cache and allocated swap alone do not establish current memory distress. Review active swap-in/out and I/O pressure alongside queues.

For Apache, obtain worker activity from `mod_status` and obtain the effective capacity limit from configuration. Account for the active MPM and treat a status-request timeout as missing evidence requiring attention. [Apache status documentation](https://httpd.apache.org/docs/2.4/mod/mod_status.html).

For FPM, collect `active processes`, `idle processes`, `listen queue`, pool identity and start time. Use the **configured maximum** as the utilisation denominator: `total processes` can vary under a dynamic manager. Historical maxima are useful evidence, but are not current utilisation. Limit indications and slow-request counts need restart-aware handling; a value left over from an old event must not generate fresh incidents indefinitely. [PHP-FPM status documentation](https://www.php.net/manual/en/fpm.status.php).

A warning should identify the node, service or pool, current value, threshold and duration. A pool warning is useful even if the rest of the server still has capacity; the server-level view determines how urgently other installations are at risk.

## Implementation sequence

### 1. Inventory the live request path

Record the hosting model, running versions, effective service names, configured worker limits, pool-to-domain mapping, scheduled tasks, existing collectors and alert rules. Verify notification recipients and delivery. Check that the person authorising the work controls the relevant server or has the provider's permission.

Include background work and its external dependencies. u5CMS uses background requests for tasks such as indexing and access updates; an ordinary page check does not automatically exercise those paths. Inspect the installed release before selecting checks. The [background transport implementation](../background-work.inc.php) is a code reference, not a monitoring endpoint.

### 2. Select a supervisor and assign ownership

Choose variant A, B or C. Identify one owner for every collector, rule, configuration file and delivery route. Run the watcher independently of Apache, PHP-FPM and interactive CMS sessions. Enable the relevant monitoring services at boot and verify their enablement without rebooting production merely to test it.

Start with **alerting and diagnostic capture**. Automatic service restarts and other corrective actions require their own operational decision and runbook. In particular, inspect any inherited Watchdog restart rules; an installation's defaults are not evidence of an agreed recovery policy.

### 3. Provide protected status collection

Use Apache's machine-readable status interface and a status source for each FPM pool. Where supported, FPM's `pm.status_listen` provides a separate status listener which remains usable when the application pool is busy. Configure an appropriate local collection route rather than sending status requests through the saturated application path. [PHP-FPM configuration](https://www.php.net/manual/en/install.fpm.configuration.php).

Bind collection to local interfaces or protected sockets where possible. Enforce restrictions at the public-facing proxy as well as the backend: a request forwarded by a local proxy can appear local to Apache. Check access **from outside** the server, including IPv6 and alternate hostnames. Detailed status pages can reveal operational information and must not become public through a rewrite or fallback route.

Use Plesk-supported configuration mechanisms. Validate syntax before a reload, identify every pool affected by a PHP-master reload, and retain a rollback copy. Do not edit generated files as if they were permanent configuration, change the PHP handler casually, or put the watcher into u5CMS `config.php`.

### 4. Implement bounded collection and alert state

Give the checker its own protected configuration and writable state directory. A useful design records normal, warning, critical and unknown states separately; unknown must not be interpreted as healthy. Persist enough state to calculate breach duration and recovery across successive invocations. Reset counter baselines when a monitored process restarts.

Limit each fetch and the whole collection cycle. Avoid overlapping invocations and unbounded retries. Ensure error handling still updates monitoring freshness and emits its own diagnostic event. Use fixed, configured endpoints rather than taking arbitrary targets from public request parameters.

Group notifications by node and incident. For example, send an opening alert, notify a severity increase, repeat a still-critical condition after 30 minutes, and send recovery after one minute of healthy samples. Keep pool identity in the diagnostic detail. Set a finite maintenance window for planned reloads and check that monitoring resumes afterwards.

### 5. Save useful evidence

On a new critical condition, save a timestamped, size-limited bundle containing the relevant metrics, the pool/domain mapping, a bounded process summary and recent pertinent error events. Record which measurements failed. Normalise timestamps or retain their time zones so logs can be correlated.

Consider FPM slow logging with an initial threshold such as 10 seconds. It can provide a PHP backtrace for a long request. Check that tracing works on this host and that the log is writable; enabling the directive alone does not prove evidence is being captured. Slow logging is observational and does not itself terminate the request. [PHP-FPM configuration](https://www.php.net/manual/en/install.fpm.configuration.php).

Store bundles outside document roots, restrict their permissions and apply rotation and retention. Strip tokens, cookies, request bodies and unnecessary query strings from notifications. Keep detailed evidence local; publish only sanitised examples. Bound evidence collection so a monitoring incident cannot fill a disk or launch repeated expensive snapshots.

### 6. Add checks from an independent location

Select a small set of safe anonymous pages covering representative websites and PHP/database paths. Validate the expected status and a stable content marker. Check whether a CDN or cache could answer without reaching PHP; an origin-level check and an external visitor check answer different questions.

Use simple HTTP checks for routine availability. Do not submit forms, send mail, perform logins or invoke a background-job/action URL as a heartbeat. A check that depends on browser JavaScript may also trigger background work. If a dedicated lightweight endpoint is needed, design it without disclosing internals or changing user data.

Send the local collector's heartbeat only after its agreed collection/evaluation cycle has completed. Include freshness and health of mandatory measurements; a loop that keeps sending while all collectors have failed is misleading. Set the missing-heartbeat deadline to allow the normal interval, network delay and a small number of missed cycles. Verify that the external service can alert when local mail or the hosting node is unavailable.

### 7. Address amplification separately

Monitoring gives an operator time to act; recurring overload still needs its cause fixed. Review request deadlines, database locking, remote-call timeouts and scheduled-task overlap. A client-side HTTP timeout does not guarantee that the server-side job has ended. A lock around a `curl` invocation covers that invocation; the lock protecting the actual job must last for the whole job.

Treat worker limits as capacity controls. Raising all pools' limits can increase memory demand and pressure on shared services. Review measured worker memory, the database and operating-system reserve, and simultaneous demand across pools before changing capacity. Separate staging and production pools help attribution; stronger isolation may require separate service instances or hosting nodes.

Review any server-side request termination separately from monitoring. PHP's `max_execution_time` does not count every kind of wait on Unix; FPM has a request-termination setting, but imposing a blanket deadline can interrupt legitimate imports, exports, forms or background work. [PHP runtime configuration](https://www.php.net/manual/en/info.configuration.php#ini.max-execution-time) and [FPM configuration](https://www.php.net/manual/en/install.fpm.configuration.php).

## Ready-to-use ChatGPT prompts

Use the prompt for the stage you need. Replace the bracketed inputs; remove an inapplicable optional line. The prompts are deliberately self-contained so they can be used in a new conversation. Read the implementation report before treating monitoring as operational.

ChatGPT needs an environment with browser or terminal tools capable of reaching the target. A normal conversation alone cannot change a server. Sign in through the available secure authentication flow; do not paste passwords, session cookies or private keys into a prompt. Provider access and organisational authority still determine what can be changed. [Trusted Device](trusted-browsers.md) can help maintain authorised **CMS browser** access; it does not grant Plesk or operating-system privileges.

### Prompt 1: read-only assessment and a concrete proposal

```text
Assess server-wide monitoring for a server hosting multiple u5CMS websites.

Target server or Plesk URL: [SERVER_OR_PLESK_URL]
Operations contact: [ALERT_EMAIL]

You are authorised to inspect the relevant hosting and monitoring configuration,
service status and operational logs. Use the secure sign-in flow if needed.
Do not change configuration, install software, restart services or send test mail.

Inventory all hosted websites and PHP-FPM pools, every PHP master/version,
shared Apache instances and their effective limits, nginx, the database,
host resources, scheduled work, current collectors and notification routes.
Confirm which administrative tools actually work. Do not infer root access
from a CMS login, or infer working alerts from a dashboard alone.

Identify gaps in early saturation detection, attribution to individual pools,
external availability, heartbeat freshness and automatic incident evidence.
Separate verified facts, reasonable hypotheses and unknowns. Do not copy
secrets or complete configuration files into the conversation.

Recommend a concrete architecture using existing monitoring where suitable.
Describe the files/services to change, supported persistence through hosting
panel updates, delivery route, costs, expected detection delays, acceptance
checks and rollback. Cover the whole server, including staging pools.
Use British English. Ask only for information or access you cannot establish.
```

### Prompt 2: implement the local watcher autonomously

```text
Implement server-wide early-warning monitoring for this u5CMS hosting server.

Target server or Plesk URL: [SERVER_OR_PLESK_URL]
Authorised alert and test-message recipient: [ALERT_EMAIL]

I authorise the read-only assessment, installation of free software from trusted
distribution/vendor sources, protected monitoring configuration, necessary
validated reloads, local diagnostic capture and labelled test alerts to the
recipient above. Use existing administrative access and secure authentication.
Do not buy subscriptions or enrol the server with a third-party service.

Inspect the live configuration first and choose the least complex suitable
variant. Reuse the existing monitoring. Identify any panel-managed Monit
instance and supported customisation mechanism before adding another watcher.
Back up the affected configuration and prepare exact rollback instructions.

Monitor shared Apache capacity and all relevant PHP-FPM pools, alongside host
and database pressure. Discover new or removed pools. Implement protected
status collection independent of application-worker availability where the
installed software supports it. Check public proxy/backend access restrictions.

Start with Apache at 70% of effective MaxRequestWorkers for two minutes
(warning), 90% for 30 seconds (critical); each FPM pool at 70% of configured
pm.max_children for one minute (warning), 90% or a non-empty listen queue for
30 seconds (critical). Add restart-aware limit-event detection, memory,
disk/inode, database and freshness rules. Adapt these initial proposals to
observed capacity and legitimate scheduled work. Record all final values. Target local
samples about every 15 seconds with per-operation and whole-cycle deadlines,
bounded concurrency and no overlapping checks. Handle missing data, process
restarts and historical counters correctly. Implement severity, deduplication,
recovery and a protected, rotated incident snapshot with a strict size budget.

Configure warning and diagnostic actions only. Preserve the existing restart
policy; if it conflicts with alert-only operation, report the conflict and
obtain a decision rather than silently changing it. Do not reboot, change
worker limits, rewrite u5CMS code/config.php, weaken authentication or alter
production jobs. Keep secrets and detailed diagnostics off public websites.

Validate configuration before applying it. Verify service enablement,
collection, freshness, fixture-based threshold transitions, alert delivery and
recovery without stressing production. Prove the actual end-to-end delays.
Document affected services, known gaps and rollback. State separately what is
live, what was verified and what still requires an external monitoring location.
Continue autonomously within this scope and use British English.
```

### Prompt 3: add the independent external check

```text
Add independent website and missing-heartbeat checks for our u5CMS hosting node.

Hosting node: [SERVER_NAME]
Approved external machine or existing monitoring account: [EXTERNAL_LOCATION]
Safe anonymous page URLs and expected markers: [URLS_AND_MARKERS]
Authorised alert and test-message recipient: [ALERT_EMAIL]

You may configure checks in the approved location and send clearly labelled
test alerts to the recipient. Inspect the plan's current limits and costs first.
Stay within the existing free entitlement; do not buy, upgrade or create a new
paid commitment. If no independent location is available, explain that gap
instead of claiming external monitoring is live.

Verify that the checks originate outside the hosting node's failure boundary.
Use bounded HTTP requests about once per minute, with a ten-second overall
deadline, expected status/content checks and no overlapping requests. Confirm
which targets actually exercise PHP/database access rather than only a cache.
Do not submit forms, run jobs or depend on a CMS/Plesk browser session.

Connect a protected heartbeat from the local collector. Send it only after
the required collection/evaluation has completed; do not mark failed or stale
mandatory metrics as healthy. Establish and document a realistic missing-beat
deadline. Keep credentials out of public URLs, logs and documentation.

Test failed checks, a missing heartbeat, one grouped alert and recovery using
controlled test inputs. Do not interrupt the production node or its websites.
Report the independent location, cadence, actual detection latency, delivery
verification, costs and remaining limitations in British English.
```

### Prompt 4: acceptance, operational handover and rollback

```text
Verify our newly configured server-wide u5CMS monitoring and produce its
operational handover. You may inspect the target, run non-disruptive checks
and send labelled test notifications to [ALERT_EMAIL].

Target server or Plesk URL: [SERVER_OR_PLESK_URL]

Use fixtures or a dedicated test checker for threshold crossings, sustained
queues, brief spikes, stale/malformed data, service-counter resets and recovery.
Do not exhaust PHP pools, stress the database, stop production services or reboot.
For a 30-worker fixture, check the chosen warning and critical boundaries and
their duration requirements. Check that one incident does not produce a mail
storm and that a new pool is discovered or explicitly flagged as unmonitored.

Verify effective sampling and alert delays, bounded runtime, no overlapping
checks, incident-bundle permissions/retention, configuration syntax, boot
enablement, and survival of documented panel regeneration/update workflows.
Check from an external location that detailed status endpoints are inaccessible
through public hostnames, proxy paths and IPv6 where applicable. If that location
is unavailable, record the unverified check rather than guessing its result.

Confirm receipt of a labelled test alert and recovery through the configured
route. Distinguish sender acceptance from confirmed mailbox delivery. Verify
external outage/heartbeat detection if configured.

Provide an inventory of components, files and services; final thresholds and
recipients; maintenance procedure; exact rollback steps; and an incident runbook.
Identify evidence for each acceptance result and every unresolved gap. Keep
operational details private and use British English.
```

### Prompt 5: investigate a warning or incident

```text
Investigate a server-wide capacity warning affecting our u5CMS hosting node.

Target: [SERVER_OR_PLESK_URL]
Approximate incident time and time zone: [TIME_AND_ZONE]

Use read-only administrative inspection and the existing incident snapshots.
Correlate shared Apache occupancy, per-pool PHP pressure, database waits,
scheduled work, external dependencies and host resources. Check monitoring
freshness and whether a gateway timeout left backend work running.

Identify the earliest supported warning, the affected services/domains and the
evidence linking them. Separate the initiating fault from later amplification;
do not treat repeated timeout messages alone as proof of a particular code bug.
Use existing slow logs when available. Preserve useful evidence before proposing
remediation and explain gaps when no trace exists.

Do not reboot, restart services, raise limits or change application/configuration
files. Propose the narrowest practical recovery action and a lasting correction,
with their likely impact. Return a concise British English incident report with
timestamps, confidence, evidence and the next decision needed from operations.
```

## Acceptance and routine operation

Treat monitoring as operational only after collection, evaluation and delivery have been verified. A successful installation command or a green dashboard is insufficient evidence of the whole path.

| Acceptance item | Evidence to retain |
| --- | --- |
| Coverage | Inventory of the shared services and pools, plus the mechanism for detecting coverage changes. |
| Early warning | Timed fixture results for warning, critical, unknown and recovery states. |
| Delivery | A labelled alert and recovery received through the agreed route; record any part that could not be confirmed. |
| Independence | Demonstrated external check and missing-heartbeat alert, or an explicit outstanding gap. |
| Bounded operation | Measured checker duration, overlap prevention, retry limits and incident-storage limits. |
| Access protection | Local collection works and detailed status/diagnostic data remain inaccessible from public paths. |
| Persistence and rollback | Service enablement and a documented, verified configuration/rollback path under the hosting model. |

Assign the runbook to a named operational owner. Review alert quality after the first normal operating period and after major traffic, PHP, hosting-panel or hardware changes. Test delivery periodically and watch the watcher for stale samples. Account for planned backups and maintenance without leaving monitoring indefinitely muted.

During an incident, capture evidence, check the pressure on shared services and identify affected pools before taking the agreed recovery action. Restarting a PHP master may affect several websites; restarting a node affects the entire estate. The runbook should make that scope visible to the operator.

## Sources and scope of the examples

Technical interfaces and product behaviour were checked against the following primary sources on 25 November 2026. Recheck version-specific behaviour and commercial terms during implementation. The architecture, thresholds and prompts in this guide are operational recommendations, not vendor defaults.

- [Plesk Monitoring](https://docs.plesk.com/en-US/obsidian/administrator-guide/statistics-and-monitoring/monitoring.68886/)
- [Plesk Watchdog](https://docs.plesk.com/en-US/obsidian/administrator-guide/server-administration/watchdog-system-monitoring-component.70443/)
- [Monit project and licensing](https://www.mmonit.com/monit/) and [Monit manual](https://www.mmonit.com/monit/documentation/monit.html)
- [Apache mod_status](https://httpd.apache.org/docs/2.4/mod/mod_status.html)
- [PHP-FPM status](https://www.php.net/manual/en/fpm.status.php), [FPM configuration](https://www.php.net/manual/en/install.fpm.configuration.php) and [PHP execution-time behaviour](https://www.php.net/manual/en/info.configuration.php#ini.max-execution-time)
- [Prometheus Alertmanager](https://prometheus.io/docs/alerting/latest/alertmanager/)
- [u5CMS background transport](../background-work.inc.php) and [Trusted Device access guidance](trusted-browsers.md)

Examples intentionally use placeholders rather than a particular organisation's server addresses, accounts or incident logs. The chapter can therefore be used for a single busy installation, dozens of installations on one node or a multi-server estate.
