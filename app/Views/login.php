<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5"> <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Přihlášení do systému</h4>
                    </div>
                    <div class="card-body">
                        
                        <div id="alertBox" class="alert alert-danger d-none"></div>

                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Emailová adresa</label>
                                <input type="email" class="form-control" id="email" name="email" required value="admin@test.cz">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Heslo</label>
                                <input type="password" class="form-control" id="password" name="password" required value="heslo123">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Přihlásit se</button>
                        </form>

                    </div>
                    <div class="card-footer text-center text-muted">
                        <small>Nemáte účet? <a href="?page=register">Zaregistrujte se</a></small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Zastaví klasické odeslání a přenačtení stránky

        const formData = new FormData(this);
        const alertBox = document.getElementById('alertBox');
        const btn = this.querySelector('button[type="submit"]');

        // Vypneme tlačítko, aby nešlo klikat víckrát
        btn.disabled = true;
        btn.textContent = "Ověřuji...";
        alertBox.classList.add('d-none'); // Schováme staré chyby

        fetch('?page=login', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Čekáme JSON odpověď z PHP
        .then(data => {
            if (data.success) {
                // OK -> Přesměrování na home
                window.location.href = '?page=home';
            } else {
                // Chyba -> Ukážeme hlášku
                alertBox.textContent = data.message;
                alertBox.classList.remove('d-none');
                btn.disabled = false;
                btn.textContent = "Přihlásit se";
            }
        })
        .catch(error => {
            console.error('Chyba:', error);
            alertBox.textContent = "Chyba komunikace se serverem. Zkontroluj konzoli (F12).";
            alertBox.classList.remove('d-none');
            btn.disabled = false;
            btn.textContent = "Přihlásit se";
        });
    });
    </script>

</body>
</html>