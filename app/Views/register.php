<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Nová registrace</h4>
                    </div>
                    <div class="card-body">
                        
                        <div id="alertBox" class="alert alert-danger d-none"></div>

                        <form id="registerForm">
                            <div class="mb-3">
                                <label class="form-label">Jméno a příjmení</label>
                                <input type="text" class="form-control" name="jmeno" required placeholder="Jan Novák">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required placeholder="jan@email.cz">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Heslo</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Heslo znovu</label>
                                <input type="password" class="form-control" name="password_confirm" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2">Zaregistrovat se</button>
                        </form>

                    </div>
                    <div class="card-footer text-center text-muted">
                        <small>Již máte účet? <a href="?page=login">Přihlásit se</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const alertBox = document.getElementById('alertBox');

        fetch('?page=register', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Registrace úspěšná! Nyní vás přesměruji na přihlášení.");
                window.location.href = '?page=login';
            } else {
                alertBox.textContent = data.message;
                alertBox.classList.remove('d-none');
            }
        });
    });
    </script>
</body>
</html>