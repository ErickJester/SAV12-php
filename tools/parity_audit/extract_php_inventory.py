#!/usr/bin/env python3
"""Extrae inventarios estáticos del proyecto PHP SAV12.
Supuestos:
- Endpoints definidos con $router->get/post/any('path','Controller','action') en public_html/index.php.
- Vistas en views/**/*.php.
- Esquema principal en database/schema.sql.
"""
from __future__ import annotations

import argparse
import json
import re
from pathlib import Path
from typing import Dict, List

ROUTE_RE = re.compile(
    r"\$router->(?P<method>get|post|any)\(\s*'(?P<path>[^']+)'\s*,\s*'(?P<controller>[^']+)'\s*,\s*'(?P<action>[^']+)'\s*\)")

CREATE_TABLE_RE = re.compile(
    r"CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(?P<name>[A-Za-z0-9_]+)`?\s*\(", re.IGNORECASE
)


def read_lines(path: Path) -> List[str]:
    return path.read_text(encoding="utf-8", errors="ignore").splitlines()


def role_hint_from_path(path: str) -> str:
    if path.startswith("/admin"):
        return "ADMIN"
    if path.startswith("/tecnico"):
        return "TECNICO"
    if path.startswith("/usuario"):
        return "USUARIO_AUTENTICADO"
    if path in ["/", "/login", "/registro", "/logout", "/403"]:
        return "PUBLICO_O_AUTENTICADO"
    return "NO_CONFIRMADO_POR_ANALISIS_ESTATICO"


def extract_endpoints(repo_root: Path) -> Dict:
    source = repo_root / "public_html" / "index.php"
    endpoints = []
    lines = read_lines(source)
    for i, line in enumerate(lines, start=1):
        m = ROUTE_RE.search(line)
        if not m:
            continue
        method = m.group("method").upper()
        if method == "ANY":
            method = "ANY"
        endpoints.append(
            {
                "method": method,
                "path": m.group("path"),
                "controller": m.group("controller"),
                "action": m.group("action"),
                "middleware_or_role": role_hint_from_path(m.group("path")),
                "source_file": str(source.relative_to(repo_root)),
                "line": i,
            }
        )
    return {"source_files": [str(source.relative_to(repo_root))], "endpoints": endpoints}


def classify_view(rel_path: str) -> Dict[str, str]:
    p = Path(rel_path)
    module = p.parts[1] if len(p.parts) > 1 else "root"
    name = p.stem
    view_type = "view"
    if module == "layouts":
        view_type = "layout"
    elif name.startswith("_"):
        view_type = "partial"
    return {"module": module, "name": name, "type": view_type}


def extract_views(repo_root: Path) -> Dict:
    views = []
    for f in sorted((repo_root / "views").glob("**/*.php")):
        rel = str(f.relative_to(repo_root))
        meta = classify_view(rel)
        views.append({"path": rel, **meta})
    return {"views": views}


def parse_column_line(line: str) -> Dict | None:
    s = line.strip().rstrip(",")
    if not s or s.startswith("--"):
        return None
    upper = s.upper()
    if upper.startswith(("PRIMARY KEY", "FOREIGN KEY", "UNIQUE", "INDEX", "KEY", "CONSTRAINT", "CHECK")):
        return None
    m = re.match(r"`?(?P<name>[A-Za-z0-9_]+)`?\s+(?P<type>[A-Za-z0-9()_',\s]+)", s)
    if not m:
        return None
    col_type = m.group("type").split(" ")[0]
    nullable = "NOT NULL" not in upper
    default = None
    d = re.search(r"DEFAULT\s+([^\s,]+(?:\s+[^\s,]+)?)", s, re.IGNORECASE)
    if d:
        default = d.group(1)
    return {
        "name": m.group("name"),
        "type": col_type,
        "nullable": nullable,
        "default": default,
        "raw": s,
    }


def extract_schema(repo_root: Path) -> Dict:
    schema = repo_root / "database" / "schema.sql"
    lines = read_lines(schema)
    tables = []
    i = 0
    while i < len(lines):
        line = lines[i]
        m = CREATE_TABLE_RE.search(line)
        if not m:
            i += 1
            continue
        tname = m.group("name")
        start_line = i + 1
        i += 1
        cols = []
        while i < len(lines):
            l = lines[i]
            if l.strip().startswith(")"):
                break
            col = parse_column_line(l)
            if col:
                cols.append(col)
            i += 1
        tables.append(
            {
                "name": tname,
                "columns": cols,
                "source_file": str(schema.relative_to(repo_root)),
                "line": start_line,
            }
        )
        i += 1
    return {"tables": tables}


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--repo-root", default=".")
    parser.add_argument("--output-dir", default="docs/parity-baseline/artifacts")
    args = parser.parse_args()

    repo_root = Path(args.repo_root).resolve()
    outdir = (repo_root / args.output_dir).resolve()
    outdir.mkdir(parents=True, exist_ok=True)

    (outdir / "php_endpoints_inventory.json").write_text(
        json.dumps(extract_endpoints(repo_root), ensure_ascii=False, indent=2), encoding="utf-8"
    )
    (outdir / "php_views_inventory.json").write_text(
        json.dumps(extract_views(repo_root), ensure_ascii=False, indent=2), encoding="utf-8"
    )
    (outdir / "php_schema_inventory.json").write_text(
        json.dumps(extract_schema(repo_root), ensure_ascii=False, indent=2), encoding="utf-8"
    )
    print(f"Inventarios PHP generados en {outdir}")


if __name__ == "__main__":
    main()
