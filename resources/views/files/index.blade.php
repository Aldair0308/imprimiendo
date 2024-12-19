<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Archivo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Subir Archivo</h1>

        @if (session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="file" class="form-label">Selecciona un archivo:</label>
                <input type="file" class="form-control" id="file" name="file" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Estado:</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="color" class="form-label">Color:</label>
                <select class="form-select" id="color" name="color" required>
                    <option value="color">Color</option>
                    <option value="black-and-white">Black and White</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="copies" class="form-label">Copias:</label>
                <input type="number" class="form-control" id="copies" name="copies" min="1" value="1"
                    required>
            </div>

            <div class="mb-3">
                <label for="session" class="form-label">ID de Sesión:</label>
                <input type="number" class="form-control" id="session" name="session" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Precio:</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" value="1.50"
                    required>
            </div>

            <button type="submit" class="btn btn-primary">Subir Archivo</button>
        </form>
    </div>
</body>

</html>
