<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --ipn-guinda:#6C1D45; --ipn-gold:#B38E5D; }
        body { min-height:100vh; margin:0; display:grid; place-items:center; background:linear-gradient(135deg,#f3f4f6 0%,#e5e7eb 100%); }
        .top-bar { position:fixed; top:0; left:0; width:100%; height:5px; background:linear-gradient(90deg,var(--ipn-guinda) 70%, var(--ipn-gold) 70%); }
        .card403 { background:#fff; border-radius:18px; box-shadow:0 12px 35px rgba(108,29,69,.12); padding:2rem; width:min(92vw, 500px); text-align:center; border-top:5px solid var(--ipn-guinda); }
        .code { font-size:4rem; font-weight:800; color:var(--ipn-guinda); line-height:1; }
    </style>
</head>
<body>
<div class="top-bar"></div>
<div class="card403">
    <div class="code">403</div>
    <h3 class="mt-3 mb-2">Acceso Denegado</h3>
    <p class="text-muted mb-4">No tienes permisos para acceder a esta página.</p>
    <a href="/" class="btn btn-dark"><i class="bi bi-house-door-fill me-1"></i> Volver al inicio</a>
</div>
</body>
</html>
