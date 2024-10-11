<?php 
    if (!isset($_GET['type'])) {
        echo "Error: sign_form.php must contain 'type' in URL params, but you don't provide it";
        http_response_code(400);
        exit;
    }
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sign form</title>
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        #theme_switcher {
            position: absolute;
            right: 1rem;
            top: 1rem;
        }
    </style>
</head>

<body>
    <button id="theme_switcher">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(126,94,255,0.78);transform: ;msFilter:;"><path d="M12 11.807A9.002 9.002 0 0 1 10.049 2a9.942 9.942 0 0 0-5.12 2.735c-3.905 3.905-3.905 10.237 0 14.142 3.906 3.906 10.237 3.905 14.143 0a9.946 9.946 0 0 0 2.735-5.119A9.003 9.003 0 0 1 12 11.807z"></path></svg>
    </button>

    <div class="form__container">
        <form id="form" class="form" action="">
            <div class="form__header">
                <div class="form__title">Sign up</div>
                <button type="button" id="pop_up__close">X</button>
            </div>

            <div class="form__body">
                <?php
                // If form for "sign up", display "username" and "phone" label-input pair in div
                if (isset($_GET['type'])) {
                    $type = $_GET['type'];

                    switch ($type) {
                        case 'sign_up':
                            echo '
                                <div>
                                    <label for="username">Username</label>
                                    <input type="text" id="username" class="form__input" name="username" placeholder="Enter username" required >
                                </div>
                                <div>
                                    <label for="phone">Phone number</label>
                                    <input type="tel" id="phone" class="form__input" name="phone" placeholder="Enter phone" required >
                                </div>
                            ';
                            break;
                        default:
                            echo "";
                    }
                }
                ?>
                
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form__input" name="email" placeholder="Enter email" required >
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" id="password" class="form__input" name="password" placeholder="Enter password" required >
                </div>
            </div>

            <div class="form__footer">
                <button id="pop_up__cancel" type="button">Cancel</button>
                <button id="pop_up__submit" type="submit">Submit</button>
            </div>
        </form>
    </div>

    <script src="../scripts/theme.js"></script>
    <script src="../scripts/form.js"></script>
</body>
</html>