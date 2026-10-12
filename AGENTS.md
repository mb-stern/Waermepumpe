# Repository workflow

- Make all code, documentation, and test changes on `development`. Inspect `main` as the compatibility reference; do not change or merge into `main` without an explicit user request.
- Use the existing checkout. Cloud tasks are isolated; do not create a Git worktree unless requested.
- Preserve the module and library identifiers and the names and types of properties still used from `main`, so existing IP-Symcon instances retain their variable assignments. Do not reset user configuration during updates.
- Consult the official Symcon documentation index at https://www.symcon.de/de/llms.txt and the relevant linked SDK documentation for API and configuration form changes.
- Validate with `php -l WP/module.php` and `php tests/regression.php`. In this cloud environment, activate PHP with `export PATH=/workspace/.cloud-tools/bin:$PATH`.
- Local regression tests use a Symcon stand-in. Real IP-Symcon behavior and device actions require a separate integration check; do not operate real devices without explicit authorization.
