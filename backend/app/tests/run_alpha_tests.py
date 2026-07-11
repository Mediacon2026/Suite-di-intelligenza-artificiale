from __future__ import annotations

import importlib.util
import inspect
import sys
from pathlib import Path


def run() -> int:
    root = Path(__file__).resolve().parents[2]
    test_root = root / "app" / "tests"
    project_root = root.parent
    if str(project_root) not in sys.path:
        sys.path.insert(0, str(project_root))
    test_files = sorted(test_root.rglob("test_*.py"))
    failures: list[tuple[str, str, BaseException]] = []
    executed = 0

    for path in test_files:
        module_name = "alpha_test_" + "_".join(path.relative_to(root).with_suffix("").parts)
        spec = importlib.util.spec_from_file_location(module_name, path)
        if not spec or not spec.loader:
            failures.append((str(path), "import", RuntimeError("Impossibile caricare il test")))
            continue
        module = importlib.util.module_from_spec(spec)
        sys.modules[module_name] = module
        try:
            spec.loader.exec_module(module)
        except Exception as exc:
            failures.append((str(path), "import", exc))
            continue

        for name, func in inspect.getmembers(module, inspect.isfunction):
            if name.startswith("test_") and len(inspect.signature(func).parameters) == 0:
                executed += 1
                try:
                    func()
                except Exception as exc:
                    failures.append((str(path), name, exc))

    print(f"Executed {executed} tests")
    if failures:
        for path, name, exc in failures:
            print(f"FAILED {path}::{name}: {exc!r}")
        return 1
    print("All tests passed")
    return 0


if __name__ == "__main__":
    raise SystemExit(run())
