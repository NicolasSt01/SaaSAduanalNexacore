import re

filepath = 'app/Http/Controllers/DashboardController.php'
with open(filepath, 'r') as f:
    content = f.read()

# 1. Reemplazar Operacion::where('tenant_id', $tenantId) que no tenga ya cancelada
content = re.sub(
    r"Operacion::where\('tenant_id', \$tenantId\)(?!->where\('estado', '!=', 'cancelada'\))",
    r"Operacion::where('tenant_id', $tenantId)->where('estado', '!=', 'cancelada')",
    content
)

# 2. Reemplazar Operacion::where($filtroCliente)
content = re.sub(
    r"Operacion::where\(\$filtroCliente\)(?!->where\('estado', '!=', 'cancelada'\))",
    r"Operacion::where($filtroCliente)->where('estado', '!=', 'cancelada')",
    content
)

# 3. Reemplazar Operacion::where('cliente_id', $user->cliente_id)
content = re.sub(
    r"Operacion::where\('cliente_id', \$user->cliente_id\)(?!->where\('estado', '!=', 'cancelada'\))",
    r"Operacion::where('cliente_id', $user->cliente_id)->where('estado', '!=', 'cancelada')",
    content
)

# 4. Reemplazar Operacion::where('cliente_id', $user->clienteId)
content = re.sub(
    r"Operacion::where\('cliente_id', \$user->clienteId\)(?!->where\('estado', '!=', 'cancelada'\))",
    r"Operacion::where('cliente_id', $user->clienteId)->where('estado', '!=', 'cancelada')",
    content
)

# 5. Reemplazar Operacion::select('aduana_id', DB::raw('count(*) as total'))
content = re.sub(
    r"Operacion::select\('aduana_id', DB::raw\(\"count\(\*\) as total\"\)\)(?!->where\('estado', '!=', 'cancelada'\))",
    r"Operacion::where('estado', '!=', 'cancelada')->select('aduana_id', DB::raw(\"count(*) as total\"))",
    content
)

# 6. Reemplazar Operacion::select('bodega_id', DB::raw('count(*) as total'))
content = re.sub(
    r"Operacion::select\('bodega_id', DB::raw\(\"count\(\*\) as total\"\)\)(?!->where\('estado', '!=', 'cancelada'\))",
    r"Operacion::where('estado', '!=', 'cancelada')->select('bodega_id', DB::raw(\"count(*) as total\"))",
    content
)

# 7. Reemplazar Operacion::join('importadores', ...)
content = re.sub(
    r"Operacion::join\('importadores', 'operaciones\.importador_id', '=', 'importadores\.id'\)(?!->where\('estado', '!=', 'cancelada'\))",
    r"Operacion::where('estado', '!=', 'cancelada')->join('importadores', 'operaciones.importador_id', '=', 'importadores.id')",
    content
)

# 8. Reemplazar Operacion::select(DB::raw('DATE(fecha_registro) as fecha'), DB::raw('count(*) as total'))
content = re.sub(
    r"Operacion::select\(DB::raw\('DATE\(fecha_registro\) as fecha'\), DB::raw\(\"count\(\*\) as total\"\)\)(?!->where\('estado', '!=', 'cancelada'\))",
    r"Operacion::where('estado', '!=', 'cancelada')->select(DB::raw('DATE(fecha_registro) as fecha'), DB::raw(\"count(*) as total\"))",
    content
)

# 9. Reemplazar DB::table('operaciones')
content = re.sub(
    r"DB::table\('operaciones'\)(?!->where\('estado', '!=', 'cancelada'\))",
    r"DB::table('operaciones')->where('estado', '!=', 'cancelada')",
    content
)

with open(filepath, 'w') as f:
    f.write(content)

print('Done')
