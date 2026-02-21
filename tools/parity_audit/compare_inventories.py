#!/usr/bin/env python3
from __future__ import annotations

import argparse
import json
from pathlib import Path


def load(p: Path):
    if not p.exists():
        return {}
    return json.loads(p.read_text(encoding="utf-8"))


def compare_sets(java_items, php_items):
    j = set(java_items)
    p = set(php_items)
    return {
        "equivalentes": sorted(j & p),
        "solo_java": sorted(j - p),
        "solo_php": sorted(p - j),
    }


def status(j_exists: bool, p_exists: bool) -> str:
    if j_exists and p_exists:
        return "EQUIVALENTE"
    if j_exists and not p_exists:
        return "FALTANTE"
    if not j_exists and p_exists:
        return "SOLO_PHP"
    return "NO_CONFIRMADO_POR_ANALISIS_ESTATICO"


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--artifacts-dir", default="docs/parity-baseline/artifacts")
    ap.add_argument("--markdown-out", default="docs/parity-baseline/artifacts/parity_diff_summary.md")
    args = ap.parse_args()

    a = Path(args.artifacts_dir)
    php_e = load(a / "php_endpoints_inventory.json").get("endpoints", [])
    java_e = load(a / "java_endpoints_inventory.json").get("endpoints", [])
    php_v = load(a / "php_views_inventory.json").get("views", [])
    java_v = load(a / "java_views_inventory.json").get("views", [])
    php_s = load(a / "php_schema_inventory.json").get("tables", [])
    java_s = load(a / "java_schema_inventory.json").get("tables", [])

    php_end_set = [f"{x.get('method','UNKNOWN')} {x.get('path') or x.get('resolved_path','')}" for x in php_e]
    java_end_set = [f"{x.get('http_method','UNKNOWN')} {x.get('resolved_path','')}" for x in java_e]
    view_set_php = [x.get("name", "") for x in php_v]
    view_set_java = [x.get("name", "") for x in java_v]
    schema_set_php = [x.get("name", "") for x in php_s]
    schema_set_java = [x.get("name", "") for x in java_s]

    c_end = compare_sets(java_end_set, php_end_set)
    c_view = compare_sets(view_set_java, view_set_php)
    c_schema = compare_sets(schema_set_java, schema_set_php)

    lines = [
        "# Resumen de comparación de inventarios",
        "",
        f"- Endpoints equivalentes: {len(c_end['equivalentes'])}",
        f"- Endpoints solo Java: {len(c_end['solo_java'])}",
        f"- Endpoints solo PHP: {len(c_end['solo_php'])}",
        f"- Vistas equivalentes (por nombre): {len(c_view['equivalentes'])}",
        f"- Vistas solo Java: {len(c_view['solo_java'])}",
        f"- Vistas solo PHP: {len(c_view['solo_php'])}",
        f"- Tablas equivalentes (por nombre): {len(c_schema['equivalentes'])}",
        f"- Tablas solo Java: {len(c_schema['solo_java'])}",
        f"- Tablas solo PHP: {len(c_schema['solo_php'])}",
        "",
        "## Detalle endpoints solo PHP",
    ]
    lines += [f"- {x}" for x in c_end["solo_php"][:50]] or ["- (ninguno)"]
    lines += ["", "## Detalle endpoints solo Java"]
    lines += [f"- {x}" for x in c_end["solo_java"][:50]] or ["- (ninguno)"]

    out = Path(args.markdown_out)
    out.write_text("\n".join(lines), encoding="utf-8")
    print("\n".join(lines[:12]))
    print(f"\nResumen completo en: {out}")


if __name__ == "__main__":
    main()
