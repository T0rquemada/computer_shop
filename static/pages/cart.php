<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Computer shop</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div id="pop_up__screen">
        <div id="pop_up__container">
            <div class="pop_up__header">
                <div id="pop_up__title"></div>
                <div id="pop_up__close">X</div>
            </div>

            <div id="pop_up__footer" class="pop_up__footer">
                <button class="pop_up__btns" id="pop_up__cancel">Cancel</button>
                <button class="pop_up__btns" id="pop_up__submit">Submit</button>
            </div>
        </div>
    </div>

    <div id='cart__container'>
        <div id="cart__items__list"></div>
    </div>

    <script src="../scripts/main.js"></script>
    <script src="../scripts/users.js"></script>
    <script src="../scripts/cart.js"></script>
</body>
</html>