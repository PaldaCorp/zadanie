<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Zadajte IČO</title>
        <!-- Vlozenie css framaworku Bootstrap-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
        <style>
            html, body, .container {
                height: 100vh;
            }
        </style>
    </head>
    <body class="bg-light">
        <div class="container">
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="formular-obal">
                    <?php if (!empty($_GET['oznamenie'])): ?> <!-- Vypis chybovych oznameni cez premennu Url ?oznamenie= -->
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['oznamenie']); ?></div> <!-- Vypis samotnej Url premennej oznamenie,
                                                                                                                  zabranuje sa vlozeniu kodu cez htmlspecialchars() -->
                    <?php endif; ?>
                    <form action="spracuj_formular.php" method="post">
                        <div class="mb-3">
                            <label for="ico" class="form-label">Zadajte IČO</label>
                            <input type="text" name="ico" id="ico" class="form-control">
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary" type="submit">Hľadaj</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>