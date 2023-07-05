<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CuraGreen</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css"
        integrity="sha512-YWzhKL2whUzgiheMoBFwW8CKV4qpHQAEuvilg9FAn5VJUDwKZZxkJNuGM4XkWuk94WCrrwslk8yWNGmY1EduTA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" href="favicon-32x32.ico" type="image/x-icon">
    <link rel="shortcut icon" href="favicon-32x32.ico" type="image/x-icon">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript">
        // JavaScript code for clearing input fields and showing confirmation popup
        $(document).ready(function () {
            let emailInput = $('#email');
            let formContainer = $('.form-container');
            $("#myForm").on('submit', function (e) {
                e.preventDefault()
                $.ajax({
                    type: "POST",
                    url: "add_participant.php",
                    data: new FormData(this),
                    dataType: "json",
                    contentType: false,
                    cache: false,
                    processData: false,
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText)
                        console.log(status)
                        console.log(error)
                    },
                    success: function (response) {
                        if (response.message == "Participation enregistrée !") {
                            $(".messagePopup").html('<p><i class="fa-regular fa-circle-check fa-flip"></i>&nbsp;' + response.message + '</p>');
                            $(".messagePopup").css("display", "block");
                            formContainer.addClass('hidden');
                            setTimeout(function () {
                                formContainer.removeClass('hidden');
                                $(".messagePopup").empty();
                            }, 5000);
                            $('#myForm')[0].reset();
                        }
                    }
                })
            })
        })
    </script>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="Logo-Version-horizontale.png" alt="Logo CuraGreen">
        </div>
        <div class="messagePopup"></div>
        <div class="form-container">
            <p>Remplissez le formulaire pour participer au jeu</p></i>
            <form id="myForm" method="post">
                <div class="input-container">
                    <div class="row">
                        <input type="text" name="name" id="name" required placeholder="NOM">
                        <input type="text" name="firstname" id="firstname" placeholder="PRENOM" required>
                    </div>
                    <div class="row">
                        <input type="tel" name="phone" id="phone" required placeholder="TELEPHONE">
                        <input type="tel" name="amount" id="amount" required placeholder="MONTANT (€)">
                    </div>
                    <input type="email" name="email" id="email" required placeholder="EMAIL">
                </div>
                <div class="rgpd">
                    <p>Vos données personnelles seront utilisées dans le cadre de ce jeu et pour d’autres raisons
                        décrites dans notre <a href="https://curagreen.fr/politique-de-confidentialite/">politique de
                            confidentialité</a>.</p>
                </div>
                <input id="submitButton" type="submit" name="submit" value="Participer">
            </form>
        </div>
    </div>
</body>

</html>