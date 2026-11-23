# Conventions & Workflow

## 1. Code Conventions 🧹

- PHP 8.4, `declare(strict_types=1);` in every app file.
- Classes are `final` unless designed for extension.
- Constructor property promotion; explicit parameter and return types everywhere.
- Array shapes documented in PHPDoc (`@param array{name: string, email: string} $data`).
- Enum cases in TitleCase (`TaskStatus::InReview`).
- Curly braces for every control structure.
- PHPDoc over inline comments; inline comments only for genuinely complex logic.
- Descriptive names: `isAssignable()`, not `check()`.
- Generate files with `php artisan make:* --no-interaction`.
- Formatting: `vendor/bin/pint --dirty --format agent` before every commit.

### Layer responsibilities

| Layer | Allowed | Not allowed |
|---|---|---|
| Controller | Call Action / Query, return Resource | Business logic, raw queries |
| FormRequest | Validation, `authorize()` via Policy | Side effects |
| Action | One use case, transactions, dispatch events | HTTP concerns (request/response) |
| Query | Read-only aggregated queries | Writes |
| Model | Relations, casts, scopes, accessors | Business workflows |
| Resource | Shape the JSON output | Queries (use loaded relations only) |

## 2. Git Conventions

### Branches

| Branch | Purpose |
|---|---|
| `main` | Always green, always deployable |
| `feature/<phase>-<name>` | One branch per phase/feature, e.g. `feature/05-auth` |
| `fix/<name>` | Bug fixes |
| `docs/<name>` | Documentation-only changes |

### Commits — [Conventional Commits](https://www.conventionalcommits.org/)

```
<type>(<scope>): <short summary>
```

| Type | Use for |
|---|---|
| `feat` | New feature |
| `fix` | Bug fix |
| `refactor` | Code change without behaviour change |
| `perf` | Performance improvement |
| `test` | Adding or fixing tests |
| `docs` | Documentation |
| `chore` | Tooling, dependencies, config |
| `ci` | CI pipeline |

Examples: `feat(tasks): enforce reviewer before in-review transition`, `perf(dashboard): cache stats per user`.

## 3. Feature Workflow

Every feature/phase goes through the same steps:

1. **Branch** from `main`: `git switch -c feature/05-auth`.
2. **Read** the matching section in [ROADMAP.md](ROADMAP.md) and the relevant rules/skills.
3. **Implement** with small, focused commits.
4. **Test**: feature + unit tests for the change; run the narrowest test set, then the full suite.
5. **Quality gate**: `pint`, `phpstan`, `rector --dry-run`, `php artisan test` all green.
6. **Document**: copy [`features/_TEMPLATE.md`](features/_TEMPLATE.md) to `features/NN-<name>.md` and fill it in.
7. **Update** the phase status in [ROADMAP.md](ROADMAP.md#13-delivery-phases).
8. **Merge** into `main` with `--no-ff` (keeps the feature visible in history).
9. **Push** `main` and the feature branch to GitHub.

## 4. Definition of Done

- [ ] Business rules from the roadmap implemented
- [ ] Policies + validation + rate limiting where relevant
- [ ] No N+1 (lazy loading prevented), indexes in place
- [ ] Tests written and passing
- [ ] Pint, Larastan, Rector clean
- [ ] Feature document written
- [ ] Roadmap status updated
- [ ] Merged and pushed
