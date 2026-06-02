import re

filepath = 'app/Http/Controllers/OperacionController.php'
with open(filepath, 'r') as f:
    content = f.read()

# Reemplazo 1: $query = Operacion::with([...]) -> agregar where estado
content = re.sub(
    r'(\$query\s*=\s*Operacion::with\(\[)',
    r"\1\n            ->where('estado', '!=', 'cancelada')",
    content
)

# Reemplazo 2: $modulacionCounts = Operacion::select(
content = re.sub(
    r'(\$modulacionCounts\s*=\s*Operacion::select\()',
    r"\1\n            ->where('estado', '!=', 'cancelada')",
    content
)

# Reemplazo 3: $registros = Operacion::with('cliente')
content = re.sub(
    r"(\$registros\s*=\s*Operacion::with\('cliente'\))",
    r"\1\n            ->where('estado', '!=', 'cancelada')",
    content
)

# Reemplazo 4: $registros = Operacion::with(['cliente', 'expediente', 'patente'])
content = re.sub(
    r"(\$registros\s*=\s*Operacion::with\(\['cliente', 'expediente', 'patente'\]\))",
    r"\1\n            ->where('estado', '!=', 'cancelada')",
    content
)

# Reemplazo 5: $statsHoy = Operacion::whereDate(
content = re.sub(
    r'(\$statsHoy\s*=\s*Operacion::whereDate\()',
    r"\1\n            ->where('estado', '!=', 'cancelada')",
    content
)

# Reemplazo 6: $topRegistradores = Operacion::select(
content = re.sub(
    r'(\$topRegistradores\s*=\s*Operacion::select\()',
    r"\1\n            ->where('estado', '!=', 'cancelada')",
    content
)

# Reemplazo 7: $topCerradores = Operacion::select(
content = re.sub(
    r'(\$topCerradores\s*=\s*Operacion::select\()',
    r"\1\n            ->where('estado', '!=', 'cancelada')",
    content
)

with open(filepath, 'w') as f:
    f.write(content)

print('Done')
