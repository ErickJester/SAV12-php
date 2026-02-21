#!/usr/bin/env python3
"""Extrae inventarios estáticos del proyecto Java de referencia.
Busca proyecto extraído o ZIP en rutas candidatas.
"""
from __future__ import annotations

import argparse
import json
import re
import tempfile
import zipfile
from pathlib import Path
from typing import Dict, List, Optional

CLASS_RE = re.compile(r"class\s+([A-Za-z0-9_]+)")
REQ_CLASS_RE = re.compile(r"@RequestMapping\(([^)]*)\)")
MAPPING_RE = re.compile(r"@(GetMapping|PostMapping|PutMapping|DeleteMapping|PatchMapping|RequestMapping)\(([^)]*)\)")
PREAUTH_RE = re.compile(r"@PreAuthorize\(([^)]*)\)")
PATH_IN_ANN_RE = re.compile(r"(?:value|path)?\s*=\s*\"([^\"]+)\"|\"([^\"]+)\"")
METHOD_IN_REQ_RE = re.compile(r"RequestMethod\.([A-Z]+)")

CREATE_TABLE_RE = re.compile(r"CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(?P<name>[A-Za-z0-9_]+)`?\s*\(", re.IGNORECASE)


def pick_java_root(explicit: Optional[str]) -> tuple[Optional[Path], Optional[Path]]:
    if explicit:
        p = Path(explicit)
        return (p if p.exists() else None, None)

    candidates = [
        Path("/mnt/data/SAV12-main (1).zip"),
        Path("/mnt/data/SAV12-main"),
        Path("/workspace/SAV12-main"),
        Path("/workspace/SAV12-main (1)"),
    ]
    for c in candidates:
        if c.is_dir():
            return c, None
        if c.is_file() and c.suffix.lower() == ".zip":
            tmp = Path(tempfile.mkdtemp(prefix="sav12_java_"))
            with zipfile.ZipFile(c, "r") as zf:
                zf.extractall(tmp)
            roots = [d for d in tmp.iterdir() if d.is_dir()]
            return (roots[0] if roots else tmp, tmp)
    return None, None


def parse_ann_paths(raw: str) -> List[str]:
    paths = []
    for m in PATH_IN_ANN_RE.finditer(raw):
        p = m.group(1) or m.group(2)
        if p:
            paths.append(p)
    return paths or [""]


def normalize_path(base: str, child: str) -> str:
    base = (base or "").strip()
    child = (child or "").strip()
    if not base.startswith("/") and base:
        base = "/" + base
    if not child.startswith("/") and child:
        child = "/" + child
    full = (base + child) or "/"
    full = re.sub(r"//+", "/", full)
    return full


def extract_endpoints(java_root: Path) -> Dict:
    endpoints = []
    source_files = []
    controllers = []
    files = list(java_root.glob("**/*Controller.java")) + list(java_root.glob("**/*Resource.java"))
    files = sorted(set(files))
    for f in files:
        rel = str(f.relative_to(java_root))
        source_files.append(rel)
        text = f.read_text(encoding="utf-8", errors="ignore")
        lines = text.splitlines()
        class_name = CLASS_RE.search(text)
        controller = class_name.group(1) if class_name else f.stem
        controllers.append(controller)

        class_map = ""
        cm = REQ_CLASS_RE.search(text)
        if cm:
            class_paths = parse_ann_paths(cm.group(1))
            class_map = class_paths[0] if class_paths else ""

        pending_method_map = None
        pending_security = None
        for i, line in enumerate(lines, start=1):
            if "@PreAuthorize" in line:
                p = PREAUTH_RE.search(line)
                if p:
                    pending_security = p.group(1)
            mm = MAPPING_RE.search(line)
            if mm:
                pending_method_map = (mm.group(1), mm.group(2), i, pending_security)
                pending_security = None
                continue
            if pending_method_map and re.search(r"\bpublic\b", line):
                ann, raw, ann_line, security = pending_method_map
                method_name_match = re.search(r"\b([A-Za-z0-9_]+)\s*\(", line)
                action = method_name_match.group(1) if method_name_match else "UNKNOWN"
                paths = parse_ann_paths(raw)
                http_method = "UNKNOWN"
                if ann == "GetMapping":
                    http_method = "GET"
                elif ann == "PostMapping":
                    http_method = "POST"
                elif ann == "PutMapping":
                    http_method = "PUT"
                elif ann == "DeleteMapping":
                    http_method = "DELETE"
                elif ann == "PatchMapping":
                    http_method = "PATCH"
                elif ann == "RequestMapping":
                    rm = METHOD_IN_REQ_RE.search(raw)
                    if rm:
                        http_method = rm.group(1)

                for p in paths:
                    endpoints.append(
                        {
                            "class_level_mapping": class_map or "",
                            "method_mapping": p or "",
                            "http_method": http_method,
                            "resolved_path": normalize_path(class_map, p),
                            "controller": controller,
                            "action": action,
                            "security_hint": security or "NO_CONFIRMADO_POR_ANALISIS_ESTATICO",
                            "source_file": rel,
                            "line": ann_line,
                        }
                    )
                pending_method_map = None

    return {"source_files": source_files, "controllers": sorted(set(controllers)), "endpoints": endpoints}


def extract_views(java_root: Path) -> Dict:
    views = []
    for f in sorted(java_root.glob("**/src/main/resources/templates/**/*.html")):
        rel = str(f.relative_to(java_root))
        template_rel = rel.split("templates/", 1)[-1]
        parts = Path(template_rel).parts
        module = parts[0] if len(parts) > 1 else "root"
        views.append({"path": rel, "module": module, "name": Path(template_rel).stem, "type": "view"})
    return {"views": views}


def parse_column_line(line: str):
    s = line.strip().rstrip(",")
    if not s:
        return None
    upper = s.upper()
    if upper.startswith(("PRIMARY KEY", "FOREIGN KEY", "UNIQUE", "INDEX", "KEY", "CONSTRAINT", "CHECK")):
        return None
    m = re.match(r"`?(?P<name>[A-Za-z0-9_]+)`?\s+(?P<type>[A-Za-z0-9()_',\s]+)", s)
    if not m:
        return None
    dtype = m.group("type").split(" ")[0]
    nullable = "NOT NULL" not in upper
    d = re.search(r"DEFAULT\s+([^\s,]+(?:\s+[^\s,]+)?)", s, re.IGNORECASE)
    default = d.group(1) if d else None
    return {"name": m.group("name"), "type": dtype, "nullable": nullable, "default": default, "raw": s}


def extract_schema(java_root: Path) -> Dict:
    candidates = sorted(
        [p for p in java_root.glob("**/*.sql") if "schema" in p.name.lower() or "V" in p.name]
    )
    tables = []
    for schema in candidates:
        lines = schema.read_text(encoding="utf-8", errors="ignore").splitlines()
        i = 0
        while i < len(lines):
            m = CREATE_TABLE_RE.search(lines[i])
            if not m:
                i += 1
                continue
            name = m.group("name")
            start_line = i + 1
            i += 1
            cols = []
            while i < len(lines):
                if lines[i].strip().startswith(")"):
                    break
                c = parse_column_line(lines[i])
                if c:
                    cols.append(c)
                i += 1
            tables.append({"name": name, "columns": cols, "source_file": str(schema.relative_to(java_root)), "line": start_line})
            i += 1
    return {"tables": tables}


def main() -> None:
    ap = argparse.ArgumentParser()
    ap.add_argument("--java-root", default=None)
    ap.add_argument("--output-dir", default="docs/parity-baseline/artifacts")
    args = ap.parse_args()

    java_root, temp_root = pick_java_root(args.java_root)
    repo_root = Path(".").resolve()
    outdir = (repo_root / args.output_dir).resolve()
    outdir.mkdir(parents=True, exist_ok=True)

    if java_root is None:
        meta = {
            "source_files": [],
            "controllers": [],
            "endpoints": [],
            "note": "NO_CONFIRMADO_POR_ANALISIS_ESTATICO: no se encontró proyecto Java/ZIP en rutas candidatas",
        }
        (outdir / "java_endpoints_inventory.json").write_text(json.dumps(meta, ensure_ascii=False, indent=2), encoding="utf-8")
        (outdir / "java_views_inventory.json").write_text(json.dumps({"views": [], "note": meta["note"]}, ensure_ascii=False, indent=2), encoding="utf-8")
        (outdir / "java_schema_inventory.json").write_text(json.dumps({"tables": [], "note": meta["note"]}, ensure_ascii=False, indent=2), encoding="utf-8")
        print(meta["note"])
        return

    (outdir / "java_endpoints_inventory.json").write_text(json.dumps(extract_endpoints(java_root), ensure_ascii=False, indent=2), encoding="utf-8")
    (outdir / "java_views_inventory.json").write_text(json.dumps(extract_views(java_root), ensure_ascii=False, indent=2), encoding="utf-8")
    (outdir / "java_schema_inventory.json").write_text(json.dumps(extract_schema(java_root), ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"Inventarios Java generados en {outdir} desde {java_root}")
    if temp_root:
        print(f"Directorio temporal usado: {temp_root}")


if __name__ == "__main__":
    main()
